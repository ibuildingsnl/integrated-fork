<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

class UsageCounter
{
    public function __construct(private readonly DocumentManager $content)
    {
    }

    public function countUsages(string $taxonomyId): int
    {
        return $this->content->createQueryBuilder(Content::class)
            ->field('relations.references.$id')
            ->equals($taxonomyId)
            ->count()
            ->hydrate(false)
            ->getQuery()
            ->execute();
    }
}
