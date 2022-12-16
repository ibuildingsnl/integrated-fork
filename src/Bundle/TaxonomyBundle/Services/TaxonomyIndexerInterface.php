<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;

interface TaxonomyIndexerInterface
{
    /** @return IndexedItem[] */
    public function buildTaxonomyIndex(): array;
}
