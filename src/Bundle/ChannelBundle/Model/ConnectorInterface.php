<?php

namespace Integrated\Bundle\ChannelBundle\Model;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

interface ConnectorInterface
{
    public function getName(): string;

    /** @throws CouldNotPublish */
    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options): ?string;
}
