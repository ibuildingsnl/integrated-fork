<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;

final class TaxonomyIndexer implements TaxonomyIndexerInterface
{
    public function __construct(
        private readonly ObjectRepository $taxonomies,
        private readonly UsageCounter $usage,
    ) {
    }

    /** @return IndexedItem[] */
    public function buildTaxonomyIndex(): array
    {
        return $this->toTreeIndex(...$this->taxonomies->findAll());
    }

    private function toTreeIndex(Taxonomy ...$taxonomies): array
    {
        $byParent = [];
        foreach ($taxonomies as $taxonomy) {
            $byParent[$taxonomy->getParentID() ?: 'root'][] = $taxonomy;
        }

        return $this->toSortedIndex($byParent);
    }

    /**
     * @param Taxonomy[][]  $byParent
     * @param string|null   $key
     * @param int           $depth
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
        return new IndexedItem(
            $taxonomy->getId(),
            $taxonomy->getTitle(),
            $taxonomy->getSlug(),
            $this->usage->countUsages($taxonomy->getId()),
            $depth
        );
    }
}
