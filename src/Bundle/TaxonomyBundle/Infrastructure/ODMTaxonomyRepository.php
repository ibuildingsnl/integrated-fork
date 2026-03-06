<?php

namespace Integrated\Bundle\TaxonomyBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Solarium\Core\Client\ClientInterface;

final class ODMTaxonomyRepository implements TaxonomyRepositoryInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly ObjectRepository $doctrineRepo,
        QueryFactoryInterface $queryFactory,
        ClientInterface $solrClient,
    ) {
        unset($queryFactory, $solrClient);
    }

    public function all(): array
    {
        return $this->doctrineRepo->findAll();
    }

    public function paged(string $contentType, int $offset, int $limit): array
    {
        $result = $this->manager->createQueryBuilder(Taxonomy::class)
            ->field('contentType')
            ->equals($contentType)
            ->sort('rank', 'asc')
            ->sort('title', 'asc')
            ->skip($offset)
            ->limit($limit)
            ->getQuery()
            ->execute();

        if (\is_array($result)) {
            $items = $result;
        } elseif ($result instanceof \Traversable) {
            $items = iterator_to_array($result, false);
        } elseif (\is_object($result) && method_exists($result, 'toArray')) {
            /** @var array<int, mixed> $items */
            $items = $result->toArray();
        } else {
            $items = [];
        }

        return array_values(array_filter($items, static fn (mixed $item): bool => $item instanceof Taxonomy));
    }

    public function byId(string $id): ?Taxonomy
    {
        return $this->doctrineRepo->find($id);
    }

    public function byType(string $contentType): array
    {
        return $this->doctrineRepo->findBy(['contentType' => $contentType]);
    }

    public function count(string $contentType): int
    {
        return $this->manager->createQueryBuilder(Content::class)
            ->field('contentType')
            ->equals($contentType)
            ->count()
            ->hydrate(false)
            ->getQuery()
            ->execute();
    }

    public function add(Taxonomy $taxonomy): void
    {
        $this->manager->persist($taxonomy);
    }

    public function countUsages(Taxonomy $taxonomy): int
    {
        return $this->manager->createQueryBuilder(Content::class)
            ->field('relations.references.$id')
            ->equals($taxonomy->getId())
            ->count()
            ->hydrate(false)
            ->getQuery()
            ->execute();
    }

    /**
     * @param array<string> $taxonomyIds
     *
     * @return array<string, int>
     */
    public function countUsagesFor(array $taxonomyIds): array
    {
        $ids = array_values(array_filter(array_map(static function (mixed $id): string {
            return trim((string) $id);
        }, $taxonomyIds), static function (string $id): bool {
            return '' !== $id;
        }));

        if ([] === $ids) {
            return [];
        }

        $counts = array_fill_keys($ids, 0);
        $rows = $this->manager->getDocumentCollection(Content::class)->aggregate([
            ['$match' => ['relations.references.$id' => ['$in' => $ids]]],
            ['$unwind' => '$relations'],
            ['$unwind' => '$relations.references'],
            ['$match' => ['relations.references.$id' => ['$in' => $ids]]],
            ['$group' => ['_id' => ['taxonomy' => '$relations.references.$id', 'content' => '$_id']]],
            ['$group' => ['_id' => '$_id.taxonomy', 'count' => ['$sum' => 1]]],
        ])->toArray();

        foreach ($rows as $row) {
            $id = trim((string) ($row['_id'] ?? ''));
            if ('' === $id) {
                continue;
            }

            $counts[$id] = (int) ($row['count'] ?? 0);
        }

        return $counts;
    }
}
