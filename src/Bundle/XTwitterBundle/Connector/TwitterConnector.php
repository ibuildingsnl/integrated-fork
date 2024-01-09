<?php

namespace Integrated\Bundle\XTwitterBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

final class TwitterConnector implements ConnectorInterface
{
    public const NAME = 'X (Twitter)';

    public function __construct(
        private readonly TwitterFactory $factory,
        private readonly LinkMaker $linkMaker,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        if (!$options->has('token') || !$options->has('token_secret')) {
            throw new CouldNotPublish('An access token and secret are required to create a twitter exporter');
        }

        $client = $this->factory->createClient($options->get('token'), $options->get('token_secret'));

        $message = [
            $settings['text'] ?? null,
            $this->linkMaker->urlFor($content, $channel),
        ];

        $response = $client->post('tweets', ['text' => implode("\n\n", $message)], true);

        if (!isset($response['data']['id'])) {
            throw new CouldNotPublish('Could not publish to twitter: '.$this->getErrorFromResponse($response));
        }

        return $response['data']['id'];
    }

    private function getErrorFromResponse(array $response): string
    {
        return $response['errors'][0]['message'] ?? $response['detail'] ?? var_export($response, true);
    }
}
