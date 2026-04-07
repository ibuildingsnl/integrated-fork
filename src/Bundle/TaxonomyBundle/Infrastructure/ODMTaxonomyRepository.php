<?php

namespace Integrated\Bundle\TaxonomyBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;

final class ODMTaxonomyRepository implements TaxonomyRepositoryInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly ObjectRepository $doctrineRepo,
    ) {
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

    public function byTypeForIndex(string $contentType): array
    {
        $rows = $this->manager->createQueryBuilder(Taxonomy::class)
            ->field('contentType')
            ->equals($contentType)
            ->select(['contentType', 'title', 'description', 'slug', 'rank', 'parent_id', 'link_to_channel', 'channels'])
            ->hydrate(false)
            ->getQuery()
            ->getIterator();

        $items = [];
        $channelIds = [];

        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }

            $items[] = $row;

            foreach ($this->extractReferenceIds($row['channels'] ?? []) as $channelId) {
                $channelIds[$channelId] = $channelId;
            }
        }

        $channelsById = $this->getChannelsById(array_values($channelIds));

        return array_values(array_filter(array_map(
            fn (array $row): ?Taxonomy => $this->buildIndexTaxonomy($row, $channelsById),
            $items
        )));
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
        $rows = iterator_to_array($this->manager->getDocumentCollection(Content::class)->aggregate([
            ['$match' => ['relations.references.$id' => ['$in' => $ids]]],
            ['$unwind' => '$relations'],
            ['$unwind' => '$relations.references'],
            ['$match' => ['relations.references.$id' => ['$in' => $ids]]],
            ['$group' => ['_id' => ['taxonomy' => '$relations.references.$id', 'content' => '$_id']]],
            ['$group' => ['_id' => '$_id.taxonomy', 'count' => ['$sum' => 1]]],
        ]), false);

        foreach ($rows as $row) {
            if (\is_array($row)) {
                $rowData = $row;
            } elseif (\is_object($row)) {
                /** @var array<string, mixed> $rowData */
                $rowData = (array) $row;
            } else {
                continue;
            }

            $id = trim((string) ($rowData['_id'] ?? ''));
            if ('' === $id) {
                continue;
            }

            $counts[$id] = (int) ($rowData['count'] ?? 0);
        }

        return $counts;
    }

    /**
     * @param array<string, Channel> $channelsById
     */
    private function buildIndexTaxonomy(array $row, array $channelsById): ?Taxonomy
    {
        $id = trim((string) ($row['_id'] ?? ''));
        if ('' === $id) {
            return null;
        }

        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setContentType(trim((string) ($row['contentType'] ?? '')));
        $taxonomy->setTitle((string) ($row['title'] ?? ''));
        $taxonomy->setDescription((string) ($row['description'] ?? ''));
        $taxonomy->setSlug((string) ($row['slug'] ?? ''));
        $taxonomy->setRank($this->normalizeNullableString($row['rank'] ?? null));
        $taxonomy->setParentID($this->normalizeNullableString($row['parent_id'] ?? null));
        $taxonomy->setLinkToChannel($this->normalizeNullableString($row['link_to_channel'] ?? null));

        $channels = [];
        foreach ($this->extractReferenceIds($row['channels'] ?? []) as $channelId) {
            if (isset($channelsById[$channelId])) {
                $channels[] = $channelsById[$channelId];
            }
        }
        $taxonomy->setChannels($channels);

        return $taxonomy;
    }

    /**
     * @param array<string> $channelIds
     *
     * @return array<string, Channel>
     */
    private function getChannelsById(array $channelIds): array
    {
        if ([] === $channelIds) {
            return [];
        }

        $repository = $this->manager->getRepository(Channel::class);
        $channels = $repository instanceof ChannelRepository
            ? $repository->findByIds($channelIds)
            : $repository->findBy(['id' => ['$in' => $channelIds]]);

        $channelsById = [];

        foreach ($channels as $channel) {
            if (!$channel instanceof Channel || null === $channel->getId()) {
                continue;
            }

            $channelsById[$channel->getId()] = $channel;
        }

        return $channelsById;
    }

    /**
     * @return string[]
     */
    private function extractReferenceIds(mixed $references): array
    {
        if (!\is_iterable($references)) {
            return [];
        }

        $ids = [];

        foreach ($references as $reference) {
            if (!\is_array($reference)) {
                continue;
            }

            $id = trim((string) ($reference['$id'] ?? ''));
            if ('' === $id) {
                continue;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (!\is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return '' !== $normalized ? $normalized : null;
    }
}
