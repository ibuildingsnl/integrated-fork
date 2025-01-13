<?php

namespace Integrated\Bundle\BrandBundle\Document;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Common\Content\Channel\ChannelInterface;

class ChannelLink
{
    private ?string $id = null;

    public function __construct(
        public ChannelType $type,
        public ?ChannelInterface $channel,
        public bool $default,
    ) {
    }

    public function getName(): string
    {
        return $this->type->name;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
