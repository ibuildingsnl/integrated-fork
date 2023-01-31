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

    /**
     * @return string
     */
    public function getTaxonomyId(): string
    {
        return $this->taxonomyId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }
}
