<?php

namespace Integrated\Bundle\WoodwingBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

final class WoodwingConnector implements ConnectorInterface
{
    public const NAME = 'woodwing';

    public function getName(): string
    {
        return self::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        // @todo instead make new WoodwingPost
        dd($content, $channel, $options, $settings);
    }
}
