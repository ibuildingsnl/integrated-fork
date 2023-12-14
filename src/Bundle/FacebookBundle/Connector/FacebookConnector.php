<?php

namespace Integrated\Bundle\FacebookBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

class FacebookConnector implements ConnectorInterface
{
    public const NAME = 'facebook';

    public function getName(): string
    {
        return static::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        if (!$options->has('token') || !$options->has('token_secret')) {
            throw new CouldNotPublish('An access token and secret are required to create a facebook exporter');
        }
    }
}
