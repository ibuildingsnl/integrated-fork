<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;

final class TaxonomyLister implements TaxonomyOverview
{
    public function __construct(
        private readonly TaxonomyRepositoryInterface $taxonomies,
    ) {
    }

    public function childrenOf(string $contentType, string $parentId): array
    {
        return [];
    }

    public function overviewFor(string $contentType, ?TaxonomyOptions $options = null): array
    {
        $taxonomies = $this->taxonomies->paged(
            $contentType,
            $options?->offset ?: 0,
            $options?->limit ?: 50,
        );
        $usageCounts = $this->taxonomies->countUsagesFor(array_map(static function (Taxonomy $taxonomy): string {
            return (string) $taxonomy->getId();
        }, $taxonomies));

        return array_map(
            fn (Taxonomy $t) => IndexedItem::basedOn($t, $usageCounts[(string) $t->getId()] ?? 0, 0),
            $taxonomies,
        );
    }

    public function countFor(string $contentType, string $filter = 'root'): int
    {
        return $this->taxonomies->count($contentType);
    }
}
