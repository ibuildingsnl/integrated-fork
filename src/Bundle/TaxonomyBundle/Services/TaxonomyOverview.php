<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Integrated\Bundle\TaxonomyBundle\Domain\IndexedItem;

interface TaxonomyOverview
{
    /** @return string[] */
    public function childrenOf(string $contentType, string $parentId): array;

    /** @return IndexedItem[] */
    public function overviewFor(string $contentType, ?TaxonomyOptions $options = null): array;

    public function countFor(string $contentType, string $filter = 'root'): int;
}
