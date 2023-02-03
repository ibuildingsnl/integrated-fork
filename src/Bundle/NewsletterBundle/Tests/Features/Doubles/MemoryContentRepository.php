<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\NewsletterBundle\Document\ContentRepository;

final class MemoryContentRepository implements ContentRepository
{
    /** @var Content[] */
    private array $content = [];

    public function latestByType(ContentType $type, int $offset = 0): ?Content
    {
        $n = 0;
        foreach ($this->content as $content) {
            if ($content->getContentType() === $type->getId() && $n++ === $offset) {
                return $content;
            }
        }

        return null;
    }

    public function add(Content $content): void
    {
        $this->content[] = $content;
        usort(
            $this->content,
            fn (Content $left, Content $right) => $right->getPublishTime() <=> $left->getPublishTime()
        );
    }
}
