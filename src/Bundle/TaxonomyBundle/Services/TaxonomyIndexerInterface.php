<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;

interface TaxonomyIndexerInterface
{
    /** @return string[] */
    public function childrenOf(string $contentType, string $parentId): array;

    /** @return IndexedItem[] */
    public function buildTaxonomyIndex(string $contentType, ?TaxonomyOptions $options = null): array;
}
