<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class TaxonomyIndexer implements TaxonomyIndexerInterface
{
    public function __construct(
        private readonly TaxonomyRepositoryInterface $taxonomies,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {
    }

    /** @return IndexedItem[] */
    public function buildTaxonomySelectOptions(string $contentType): array
    {
        $taxonomies = [];

        foreach ($this->taxonomies->byType($contentType) as $taxonomy) {
            if ($taxonomy->getParentID() === null) {
                $taxonomies[$taxonomy->getId()] = [
                    'id' => $taxonomy->getId(),
                    'name' => $taxonomy->getTitle()
                ];
            }
        }

        return $taxonomies;
    }

    public function childrenOf(string $contentType, string $parentId): array
    {
        return $this->listByParent($contentType)[$parentId] ?? [];
    }

    /** @return IndexedItem[] */
    public function listByParent(string $contentType): array
    {
        $byParent = [];

        foreach ($this->taxonomies->byType($contentType) as $taxonomy) {
            if ($this->authorization->isGranted('view', $taxonomy)) {
                $byParent[$taxonomy->getParentID() ?: 'root'][] = $taxonomy;
            }
        }

        return $byParent;
    }

    /** @return IndexedItem[] */
    public function buildTaxonomyIndex(string $contentType, string $root = 'root', bool $filtered = false): array
    {
        return $this->toSortedIndex(
            $this->listByParent($contentType),
            $root,
            $filtered ? 1 : 0,
            $filtered ? [$this->toIndexed($this->taxonomies->byId($root))] : []
        );
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
            $depth,
            $taxonomy->getChannels(),
        );
    }
}
