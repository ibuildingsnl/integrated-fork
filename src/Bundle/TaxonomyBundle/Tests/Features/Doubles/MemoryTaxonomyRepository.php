<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepository;

final class MemoryTaxonomyRepository implements TaxonomyRepository
{
    /** @var Taxonomy[] */
    private array $taxonomies = [];
    /** @var int[] */
    private array $usages = [];

    public function all(): array
    {
        return $this->taxonomies;
    }

    public function add(Taxonomy $taxonomy): void
    {
        $this->taxonomies[] = $taxonomy;
    }

    public function countUsages(Taxonomy $taxonomy): int
    {
        return $this->usages[$taxonomy->getId()] ?? 0;
    }

    public function setUsageCount(string $taxonomyId, int $usages): void
    {
        $this->usages[$taxonomyId] = $usages;
    }
}
