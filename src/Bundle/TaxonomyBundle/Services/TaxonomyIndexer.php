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
        $root = $options->root ?: 'root';
        $filtered = $root !== 'root';

        $taxonomy = $this->taxonomies->byId($root);

        if (!$taxonomy && $filtered) {
            return [];
        }

        $byParent = $this->listByParent($contentType);
        $usageCounts = $options->includeUsageCounts
            ? $this->taxonomies->countUsagesFor($this->collectTaxonomyIds($byParent, $root, $filtered))
            : [];

        return $this->slice($options, ...$this->toSortedIndex(
            $byParent,
            $root,
            $filtered ? 1 : 0,
            $filtered ? [$this->toIndexed($taxonomy, 0, $usageCounts, $options->includeUsageCounts)] : [],
            $usageCounts,
            $options->includeUsageCounts,
        ));
    }

    public function countFor(string $contentType, string $filter = 'root'): int
    {
        $root = $filter ?: 'root';
        $filtered = $root !== 'root';

        $taxonomy = $this->taxonomies->byId($root);
        if (!$taxonomy && $filtered) {
            return 0;
        }

        $byParent = $this->listByParent($contentType);
        $descendants = $this->countByParent($byParent, $root);

        return $filtered ? 1 + $descendants : $descendants;
    }

    /**
     * @param Taxonomy[][] $byParent
     */
    private function countByParent(array $byParent, ?string $key): int
    {
        $visited = [];

        $countByParent = function (?string $current) use (&$countByParent, $byParent, &$visited): int {
            if (null === $current || !isset($byParent[$current])) {
                return 0;
            }

            $visitedKey = 'node:'.$current;
            if (isset($visited[$visitedKey])) {
                return 0;
            }
            $visited[$visitedKey] = true;

            $count = 0;
            foreach ($byParent[$current] as $taxonomy) {
                $taxonomyId = trim((string) $taxonomy->getId());
                if ('' === $taxonomyId || isset($visited['node:'.$taxonomyId])) {
                    continue;
                }

                ++$count;
                $count += $countByParent($taxonomyId);
            }

            return $count;
        };

        if (null === $key) {
            return 0;
        }

        return $countByParent($key);
    }

    private function slice(TaxonomyOptions $options, IndexedItem ...$items): array
    {
        return \array_slice($items, $options->offset, $options->limit);
    }

    /** @return Taxonomy[][] */
    private function listByParent(string $contentType): array
    {
        if (isset($this->byParentCache[$contentType])) {
            return $this->byParentCache[$contentType];
        }

        $byParent = [];

        foreach ($this->taxonomies->byType($contentType) as $taxonomy) {
            if ($this->authorization->isGranted('view', $taxonomy)) {
                $byParent[$taxonomy->getParentID() ?: 'root'][$taxonomy->getId()] = $taxonomy;
            }
        }

        $this->byParentCache[$contentType] = $byParent;

        return $byParent;
    }

    /**
     * @param array<string, Taxonomy[]> $byParent
     * @param array<string, int>        $usageCounts
     * @param IndexedItem[]             $sorted
     *
     * @return IndexedItem[]
     */
    private function toSortedIndex(
        array $byParent,
        ?string $key,
        int $depth,
        array $sorted,
        array $usageCounts,
        bool $includeUsageCounts
    ): array
    {
        $visited = [];

        $walk = function (?string $current, int $currentDepth, bool $virtualRoot = false) use (&$walk, &$sorted, &$visited, $byParent, $usageCounts, $includeUsageCounts): void {
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

                $sorted[] = $this->toIndexed($taxonomy, $currentDepth, $usageCounts, $includeUsageCounts);
                $walk($taxonomyId, $currentDepth + 1, false);
            }
        };

        $walk($key, $depth, 'root' === $key);

        return $sorted;
    }

    /**
     * @param Taxonomy[][] $byParent
     *
     * @return string[]
     */
    private function collectTaxonomyIds(array $byParent, string $root, bool $filtered): array
    {
        $ids = [];
        $visited = [];

        if ($filtered && 'root' !== $root) {
            $ids[$root] = true;
        }

        $walk = function (?string $current, bool $virtualRoot = false) use (&$walk, $byParent, &$ids, &$visited): void {
            if (null === $current || !isset($byParent[$current])) {
                return;
            }

            $visitedKey = $virtualRoot ? '__root__' : 'node:'.$current;
            if (isset($visited[$visitedKey])) {
                return;
            }
            $visited[$visitedKey] = true;

            foreach ($byParent[$current] as $taxonomy) {
                $id = trim((string) $taxonomy->getId());
                if ('' === $id) {
                    continue;
                }

                $ids[$id] = true;
                $walk($id);
            }
        };

        $walk($root, 'root' === $root);

        return array_keys($ids);
    }

    /**
     * @param array<string, int> $usageCounts
     */
    private function toIndexed(
        Taxonomy $taxonomy,
        int $depth = 0,
        array $usageCounts = [],
        bool $includeUsageCounts = true
    ): IndexedItem
    {
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
}
