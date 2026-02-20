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
        return array_map(fn (Taxonomy $t) => $t->getTitle(), $this->listByParent($contentType)[$parentId] ?? []);
    }

    /** @return IndexedItem[] */
    public function overviewFor(string $contentType, ?TaxonomyOptions $options = null): array
    {
        $root = $options?->root ?: 'root';
        $filtered = $root !== 'root';

        $taxonomy = $this->taxonomies->byId($root);

        if (!$taxonomy && $filtered) {
            return [];
        }

        return $this->slice($options ?: new TaxonomyOptions(), ...$this->toSortedIndex(
            $this->listByParent($contentType),
            $root,
            $filtered ? 1 : 0,
            $filtered ? [$this->toIndexed($taxonomy)] : []
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
        if (null === $key || !isset($byParent[$key])) {
            return 0;
        }

        $count = 0;
        foreach ($byParent[$key] as $taxonomy) {
            $count++;
            $count += $this->countByParent($byParent, $taxonomy->getId());
        }

        return $count;
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
     * @param Taxonomy[][]  $byParent
     * @param IndexedItem[] $sorted
     *
     * @return IndexedItem[]
     */
    private function toSortedIndex(array $byParent, ?string $key = 'root', int $depth = 0, array $sorted = []): array
    {
        if (null === $key || !isset($byParent[$key])) {
            return $sorted;
        }
        usort(
            $byParent[$key],
            fn (Taxonomy $a, Taxonomy $b) => $a->getRank() !== $b->getRank() ?
                $a->getRank() <=> $b->getRank() :
                $a->getTitle() <=> $b->getTitle()
        );
        foreach ($byParent[$key] as $taxonomy) {
            $sorted[] = $this->toIndexed($taxonomy, $depth);
            $sorted = $this->toSortedIndex($byParent, $taxonomy->getId(), $depth + 1, $sorted);
        }

        return $sorted;
    }

    private function toIndexed(Taxonomy $taxonomy, int $depth = 0): IndexedItem
    {
        return IndexedItem::basedOn($taxonomy, $this->taxonomies->countUsages($taxonomy), $depth);
    }
}
