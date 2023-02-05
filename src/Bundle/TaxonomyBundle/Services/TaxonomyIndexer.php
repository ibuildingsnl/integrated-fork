<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepository;

final class TaxonomyIndexer implements TaxonomyIndexerInterface
{
    public function __construct(
        private readonly TaxonomyRepository $taxonomies,
    ) {
    }

    /** @return IndexedItem[] */
    public function buildTaxonomyIndex(string $contentType): array
    {
        $byParent = [];

        foreach ($this->taxonomies->byType($contentType) as $taxonomy) {
            $byParent[$taxonomy->getParentID() ?: 'root'][] = $taxonomy;
        }

        return $this->toSortedIndex($byParent);
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
        return new IndexedItem(
            $taxonomy->getId(),
            $taxonomy->getTitle(),
            $taxonomy->getDescription(),
            $taxonomy->getSlug(),
            $this->taxonomies->countUsages($taxonomy),
            $depth
        );
    }
}
