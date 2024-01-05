<?php

namespace Integrated\Bundle\XTwitterBundle\Connector;

use Abraham\TwitterOAuth\TwitterOAuthException;
use Integrated\Bundle\ChannelBundle\Event\ConfigEvent;
use Integrated\Bundle\ChannelBundle\Model\ConfigurationException;
use Integrated\Bundle\ChannelBundle\Model\OauthConfigInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TwitterConfiguration implements OauthConfigInterface
{
    public function __construct(
        private readonly TwitterFactory $factory,
        private readonly UrlGeneratorInterface $generator,
    ) {
    }

    public function getName(): string
    {
        return TwitterConnector::NAME;
    }

    public function getForm(): string
    {
        return TwitterConfigType::class;
    }

    public function prepareAuthLink(ConfigEvent $event, OptionsInterface $options): ?string
    {
        if ($options->has('token') && $options->has('token_secret')) {
            return null;
        }

        $client = $this->factory->createClient();

        try {
            $response = $client->oauth(
                'oauth/request_token',
                ['oauth_callback' => $this->generator->generate(
                    'integrated_channel_config_external_return',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )],
            );
        } catch (TwitterOAuthException $e) {
            throw ConfigurationException::encountered($e);
        }

        $options->set('request_token', $response['oauth_token']);
        $options->set('request_token_secret', $response['oauth_token_secret']);

        return $client->url('oauth/authorize', ['oauth_token' => $response['oauth_token']]);
    }

    public function handleCallback(ConfigEvent $event, OptionsInterface $options): bool
    {
        if (!$options->has('request_token') || !$options->has('request_token_secret')) {
            return false;
        }

        $verifier = $event->getRequest()->get('oauth_verifier');

        if (!$verifier) {
            return false;
        }

        try {
            $response = $this->factory->createClient($options->get('request_token'), $options->get('request_token_secret'))
                ->oauth('oauth/access_token', ['oauth_verifier' => $verifier]);
        } catch (TwitterOAuthException $e) {
            throw ConfigurationException::encountered($e);
        }

        $options
            ->set('token', $response['oauth_token'])
            ->set('token_secret', $response['oauth_token_secret'])
            ->remove('request_token')
            ->remove('request_token_secret');

        return true;
    }
}
