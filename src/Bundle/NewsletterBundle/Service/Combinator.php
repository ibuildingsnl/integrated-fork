<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Integrated\Bundle\NewsletterBundle\Document\ContentRepository;

class Combinator
{
    public function __construct(
        private readonly ContentRepository $repository,
    ) {}

    public function combine(ContentType ...$types): CombinedContent
    {
        $offsets = [];
        $content = new CombinedContent();
        foreach ($types as $type) {
            $content->addContent(
                $this->repository->latestByType($type, $offsets[$type->getId()] ?? 0)
            );
            $offsets[$type->getId()] = ($offsets[$type->getId()] ?? 0) + 1;
        }
        return $content;
    }
}
