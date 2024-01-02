<?php

namespace Integrated\Bundle\InstagramBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

class InstagramConnector implements ConnectorInterface
{
    public const NAME = 'instagram';

    public function __construct(
        private readonly InstagramClient $client,
        private readonly LinkMaker $linkMaker,
    )
    {
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        if (!$options->has('page_token')) {
            throw new CouldNotPublish('An access token and secret are required to create a facebook exporter');
        }

        return $this->client->postToPage(
            $options['page_token'],
            $options['page'],
            $settings['title'],
            $settings['text'],
            $this->linkMaker->urlFor($content, $channel)
        );
    }
}
