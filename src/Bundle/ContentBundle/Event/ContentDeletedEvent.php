<?php

namespace Integrated\Bundle\ContentBundle\Event;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Symfony\Contracts\EventDispatcher\Event;

class ContentDeletedEvent extends Event
{
    public const CONTENT_DELETED = 'content.deleted';

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
