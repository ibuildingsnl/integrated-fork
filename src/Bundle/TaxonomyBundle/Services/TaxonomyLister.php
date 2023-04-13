<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;

final class TaxonomyLister implements TaxonomyIndexerInterface
{
    public function __construct(
        private readonly TaxonomyRepositoryInterface $taxonomies,
    ) {
    }

    public function childrenOf(string $contentType, string $parentId): array
    {
        return [];
    }

    public function buildTaxonomyIndex(string $contentType, ?TaxonomyOptions $options = null): array
    {
        // @todo map to indexed item!
        return $this->taxonomies->slice(
            $contentType,
            (($options?->page ?: 1) - 1) * ($options?->pageSize ?: 50),
            $options?->pageSize ?: 50,
        );
    }
}
