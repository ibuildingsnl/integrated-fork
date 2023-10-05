<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

final class LinkedInConnector implements ConnectorInterface
{
    public const NAME = 'LinkedIn';

    public function __construct(
        private readonly LinkedInFactory $factory,
        private readonly LinkMaker $linkMaker,
    ) {}

    public function getName(): string
    {
        return self::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        if (!$options->has('token') || !$options->has('token_secret')) {
            throw new CouldNotPublish('An access token and secret are required to create a LinkedIn exporter');
        }

//        dd($settings, $content);
        $client = $this->factory->createClient($options->get('token'), $options->get('token_secret'));

        $message = $settings['foo'] ?? '';
        if (!empty($message)) {
            $message .= "\n\n";
        }
        $message .= "https://".$this->linkMaker->urlFor($content, $channel);

        $response = $client->post('tweets', ['text' => $message], true);

        if (!isset($response['data']['id'])) {
            throw new CouldNotPublish('Could not publish to LinkedIn: ' . $this->getErrorFromResponse($response));
        }

        return $response['data']['id'];
    }

    private function getErrorFromResponse(array $response): string
    {
        return $response['errors'][0]['message'] ?? $response['detail'] ?? var_export($response, true);
    }
}
