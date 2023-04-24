<?php

namespace Integrated\Bundle\TaxonomyBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Solarium\Core\Client\ClientInterface;
use Solarium\QueryType\Select\Result\Document;

final class ODMTaxonomyRepository implements TaxonomyRepositoryInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly ObjectRepository $doctrineRepo,
        private readonly QueryFactoryInterface $queryFactory,
        private readonly ClientInterface $solrClient,
    ) {
    }

    public function all(): array
    {
        return $this->doctrineRepo->findAll();
    }

    public function paged(string $contentType, int $offset, int $limit): array
    {
        $this->solrClient->getPlugin('postbigrequest');

        $query = $this->queryFactory
            ->createQuery(IntegratedContent::class, [
                'contenttypes' => [$contentType],
                'sort' => 'title',
            ])
            ->getQuery()
            ->setStart($offset)
            ->setRows($limit);

        /** @var Document[] $items */
        $items = $this->solrClient->select($query)->getDocuments();

        return array_map(fn (Document $document) => $this->load($document, $contentType), $items);
    }

    private function load(Document $document, string $type): Taxonomy
    {
        try {
            return $this->byId($document['type_id']);
        } catch (\TypeError $e) {
            throw new \RuntimeException("$type item `{$document['type_id']}` not found in database");
        }
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
}
