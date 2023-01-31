<?php

namespace Integrated\Bundle\TaxonomyBundle\Domain;

final class IndexedItem
{
    public function __construct(
        private readonly string $taxonomyId,
        private readonly string $title,
        private readonly string $slug,
        private readonly int $count,
        private readonly int $depth,
    ) {
    }

    public function getTaxonomyId(): string
    {
        return $this->taxonomyId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }
}
