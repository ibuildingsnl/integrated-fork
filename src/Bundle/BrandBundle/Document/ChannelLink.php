<?php

namespace Integrated\Bundle\BrandBundle\Document;

use Integrated\Common\Channel\ChannelInterface;

class ChannelLink
{
    private ?string $id = null;
    public function __construct(
        public LinkType $type,
        public ChannelInterface $channel,
        public bool $default,
    ){}
}
