<?php

namespace Integrated\Bundle\TaxonomyBundle\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;

final class IndexedItem
{
    public function __construct(
        private readonly string $taxonomyId,
        private readonly string|null $title,
        private readonly string|null $description,
        private readonly string|null $slug,
        private readonly array $channels,
        private readonly ArrayCollection $references,
        private readonly string|null $linkToChannel,
        private readonly int $count,
        private readonly int $depth,
    ) {
    }

    public static function basedOn(Taxonomy $taxonomy, int $usageCount, int $depth): self
    {
        return new self(
            $taxonomy->getId(),
            $taxonomy->getTitle(),
            $taxonomy->getDescription(),
            $taxonomy->getSlug(),
            $taxonomy->getChannels(),
            $taxonomy->getReferencesByRelationId('__children'),
            $taxonomy->getLinkToChannel(),
            $usageCount,
            $depth,
        );
    }

    public function getTaxonomyId(): string
    {
        return $this->taxonomyId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function getReferences(): ArrayCollection
    {
        return $this->references;
    }

    public function getLinkToChannel(): ?string
    {
        return $this->linkToChannel;
    }

    public function getSlug(): ?string
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
