<?php

namespace Integrated\Bundle\TaxonomyBundle\Domain;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;

interface TaxonomyRepository
{
    /** @return Taxonomy[] */
    public function all(): array;

    public function add(Taxonomy $taxonomy): void;

    public function countUsages(Taxonomy $taxonomy): int;
}
