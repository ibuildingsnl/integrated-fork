<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class TaxonomyIndexer implements TaxonomyOverview
{
    /** @var array<string, array<string, Taxonomy[]>> */
    private array $byParentCache = [];
    /** @var array<string, array<int, array{taxonomy: Taxonomy, depth: int}>> */
    private array $entriesCache = [];
    /** @var array<string, bool> */
    private array $viewableCache = [];

    public function __construct(
        private readonly TaxonomyRepositoryInterface $taxonomies,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {
    }

    /** @return string[] */
    public function childrenOf(string $contentType, string $parentId): array
    {
        $children = $this->listByParent($contentType)[$parentId] ?? [];
        uasort($children, fn (Taxonomy $a, Taxonomy $b) => $this->compareTaxonomy($a, $b));

        return array_map(fn (Taxonomy $t) => $t->getTitle(), $children);
    }

    /** @return IndexedItem[] */
    public function overviewFor(string $contentType, ?TaxonomyOptions $options = null): array
    {
        $options ??= new TaxonomyOptions();
        $entries = $this->entriesFor($contentType, $options->root ?: 'root');

        if ([] === $entries) {
            return [];
        }

        $usageCounts = $options->includeUsageCounts
            ? $this->taxonomies->countUsagesFor(array_map(
                static fn (array $entry): string => (string) $entry['taxonomy']->getId(),
                $entries,
            ))
            : [];

        return array_map(
            fn (array $entry): IndexedItem => $this->toIndexed(
                $entry['taxonomy'],
                $entry['depth'],
                $usageCounts,
                $options->includeUsageCounts,
            ),
            array_slice($entries, $options->offset, $options->limit),
        );
    }

    public function countFor(string $contentType, string $filter = 'root'): int
    {
        return \count($this->entriesFor($contentType, $filter ?: 'root'));
    }

    /** @return Taxonomy[][] */
    private function listByParent(string $contentType): array
    {
        if (isset($this->byParentCache[$contentType])) {
            return $this->byParentCache[$contentType];
        }

        $byParent = [];

        foreach ($this->taxonomies->byTypeForIndex($contentType) as $taxonomy) {
            if ($this->canView($taxonomy)) {
                $byParent[$taxonomy->getParentID() ?: 'root'][$taxonomy->getId()] = $taxonomy;
            }
        }

        $this->byParentCache[$contentType] = $byParent;

        return $byParent;
    }

    /**
     * @param array<string, Taxonomy[]> $byParent
     * @param array<int, array{taxonomy: Taxonomy, depth: int}> $entries
     *
     * @return array<int, array{taxonomy: Taxonomy, depth: int}>
     */
    private function buildEntries(
        array $byParent,
        ?string $key,
        int $depth,
        array $entries,
    ): array {
        $visited = [];

        $walk = function (?string $current, int $currentDepth, bool $virtualRoot = false) use (&$walk, &$entries, &$visited, $byParent): void {
            if (null === $current || !isset($byParent[$current])) {
                return;
            }

            $visitedKey = $virtualRoot ? '__root__' : 'node:'.$current;
            if (isset($visited[$visitedKey])) {
                return;
            }
            $visited[$visitedKey] = true;

            $children = $byParent[$current];
            usort($children, fn (Taxonomy $a, Taxonomy $b) => $this->compareTaxonomy($a, $b));

            foreach ($children as $taxonomy) {
                $taxonomyId = (string) $taxonomy->getId();
                if ('' === $taxonomyId || isset($visited['node:'.$taxonomyId])) {
                    continue;
                }

                $entries[] = [
                    'taxonomy' => $taxonomy,
                    'depth' => $currentDepth,
                ];
                $walk($taxonomyId, $currentDepth + 1, false);
            }
        };

        $walk($key, $depth, 'root' === $key);

        return $entries;
    }

    /**
     * @param array<string, int> $usageCounts
     */
    private function toIndexed(
        Taxonomy $taxonomy,
        int $depth = 0,
        array $usageCounts = [],
        bool $includeUsageCounts = true,
    ): IndexedItem {
        $id = (string) $taxonomy->getId();
        $count = $includeUsageCounts
            ? ($usageCounts[$id] ?? $this->taxonomies->countUsages($taxonomy))
            : 0;

        return IndexedItem::basedOn($taxonomy, $count, $depth);
    }

    private function compareTaxonomy(Taxonomy $a, Taxonomy $b): int
    {
        $rank = $a->getRank() <=> $b->getRank();
        if (0 !== $rank) {
            return $rank;
        }

        $title = strcasecmp((string) $a->getTitle(), (string) $b->getTitle());
        if (0 !== $title) {
            return $title;
        }

        return strcmp((string) $a->getId(), (string) $b->getId());
    }

    /**
     * @return array<int, array{taxonomy: Taxonomy, depth: int}>
     */
    private function entriesFor(string $contentType, string $root): array
    {
        $cacheKey = $contentType.'|'.$root;
        if (isset($this->entriesCache[$cacheKey])) {
            return $this->entriesCache[$cacheKey];
        }

        $filtered = 'root' !== $root;
        $taxonomy = $filtered ? $this->taxonomies->byId($root) : null;
        if ($filtered && !$taxonomy) {
            return $this->entriesCache[$cacheKey] = [];
        }

        $entries = $filtered
            ? [['taxonomy' => $taxonomy, 'depth' => 0]]
            : [];

        return $this->entriesCache[$cacheKey] = $this->buildEntries(
            $this->listByParent($contentType),
            $root,
            $filtered ? 1 : 0,
            $entries,
        );
    }

    private function canView(Taxonomy $taxonomy): bool
    {
        $signature = $this->visibilitySignature($taxonomy);
        if (isset($this->viewableCache[$signature])) {
            return $this->viewableCache[$signature];
        }

        return $this->viewableCache[$signature] = $this->authorization->isGranted('view', $taxonomy);
    }

    private function visibilitySignature(Taxonomy $taxonomy): string
    {
        $channelIds = array_map(
            static fn (mixed $channel): string => trim((string) $channel?->getId()),
            $taxonomy->getChannels()
        );
        $channelIds = array_values(array_filter($channelIds, static fn (string $id): bool => '' !== $id));
        sort($channelIds);

        return $taxonomy->getContentType().'|'.implode('|', $channelIds);
    }
}
