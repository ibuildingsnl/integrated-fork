<?php

namespace Integrated\Bundle\NewsletterBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\ContentRepository;

final class ODMContentRepository implements ContentRepository
{
    public function __construct(
        private readonly DocumentManager $doctrine,
    ) {
    }

    public function mostRecentlyPublished(ContentType $type, int $offset = 0): ?Content
    {
        $result = $this->doctrine->createQueryBuilder(Content::class)
            ->field('contentType')->equals($type->getId())
            ->field('publishTime.startDate')->lte(new \DateTime())
            ->field('publishTime.endDate')->gte(new \DateTime())
            ->sort('publishTime.startDate', 'desc')
            ->skip($offset)
            ->getQuery()
            ->getSingleResult();
        if (!$result instanceof Content) {
            return null;
        }
        return $result;
    }

    public function add(Content $content): void
    {
        $this->doctrine->persist($content);
    }
}
