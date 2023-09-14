<?php

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Content\PublishTimeInterface;

class Publication
{
    private string $id;
    public function __construct(
        public readonly Content $content,
        public readonly ChannelInterface $channel,
        public readonly PublishTimeInterface $time,
        public readonly array $settings = [],
    ) {
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
