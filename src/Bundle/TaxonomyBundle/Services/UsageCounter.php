<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Selectable;

class UsageCounter
{
    public function __construct(private readonly Selectable $content) {}

    public function countUsages(string $taxonomyId): int
    {
        return count($this->content->matching(new Criteria(
            new Comparison('relations.references.$id', '=', $taxonomyId)
        )));
    }
}
