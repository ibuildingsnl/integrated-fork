<?php

namespace Integrated\Bundle\BrandBundle\Document;

use Integrated\Common\Content\Channel\ChannelInterface;

class ChannelLink
{
    private ?string $id = null;

    public function __construct(
        public LinkType $type,
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
