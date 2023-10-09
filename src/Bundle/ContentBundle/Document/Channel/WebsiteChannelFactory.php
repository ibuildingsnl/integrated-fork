<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Integrated\Common\Content\Channel\ChannelInterface;

class WebsiteChannelFactory implements ChannelFactoryInterface
{
    public function supportedTypes(): array
    {
        return ['website'];
    }

    public function create(string $type): ChannelInterface
    {
        return new WebsiteChannel();
    }
}
