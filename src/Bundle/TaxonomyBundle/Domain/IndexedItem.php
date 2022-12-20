<?php

namespace Integrated\Bundle\TaxonomyBundle\Domain;

final class IndexedItem
{
    public function __construct(
        public readonly string $taxonomyId,
        public readonly string $title,
        public readonly string $slug,
        public readonly int    $count,
        public readonly int    $depth,
    )
    {
    }
}
