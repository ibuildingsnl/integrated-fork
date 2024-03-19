<?php

namespace Integrated\Bundle\ContentBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

class ContentDistributedEvent extends Event
{
    public const CONTENT_DISTRIBUTED = 'content.distributed';

    private Content $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}
