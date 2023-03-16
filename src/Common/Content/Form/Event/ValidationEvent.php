<?php

namespace Integrated\Common\Content\Form\Event;

use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;

class ValidationEvent extends FormEvent
{
    public function __construct(
        ContentTypeInterface $contentType,
        MetadataInterface $metadata,
        private readonly ContentInterface $content,
    ) {
        parent::__construct($contentType, $metadata);
    }

    public function getContent(): ContentInterface
    {
        return $this->content;
    }
}
