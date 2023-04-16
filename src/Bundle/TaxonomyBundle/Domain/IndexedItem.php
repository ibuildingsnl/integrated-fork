<?php

namespace Integrated\Bundle\TaxonomyBundle\Domain;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;

final class IndexedItem
{
    public function __construct(
        private readonly string $taxonomyId,
        private readonly string|null $title,
        private readonly string|null $description,
        private readonly string|null $slug,
        private readonly int $count,
        private readonly int $depth,
        private readonly array $channels,
    ) {
    }

    public static function basedOn(Taxonomy $taxonomy, int $usageCount, int $depth): self
    {
        return new self(
            $taxonomy->getId(),
            $taxonomy->getTitle(),
            $taxonomy->getDescription(),
            $taxonomy->getSlug(),
            $usageCount,
            $depth,
            $taxonomy->getChannels(),
        );
    }

    public function getTaxonomyId(): string
    {
        return $this->taxonomyId;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getSlug(): string|null
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

    public function getChannels(): array
    {
        return $this->channels;
    }
}
