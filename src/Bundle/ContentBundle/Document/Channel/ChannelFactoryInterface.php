<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Integrated\Common\Content\Channel\ChannelInterface;

interface ChannelFactoryInterface
{
    /** @return string[] */
    public function supportedTypes(): array;
    public function create(string $type): ChannelInterface;
}
