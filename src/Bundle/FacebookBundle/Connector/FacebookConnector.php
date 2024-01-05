<?php

namespace Integrated\Bundle\FacebookBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

class FacebookConnector implements ConnectorInterface
{
    public const NAME = 'facebook';

    public function __construct(
        private readonly FacebookClient $client,
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
            $options['page']['choice'],
            $settings['title'],
            $settings['text'],
            $this->linkMaker->urlFor($content, $channel)
        );
    }
}
