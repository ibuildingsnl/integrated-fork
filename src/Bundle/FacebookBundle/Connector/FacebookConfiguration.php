<?php

namespace Integrated\Bundle\FacebookBundle\Connector;

use GuzzleHttp\Exception\RequestException;
use Integrated\Bundle\ChannelBundle\Event\ConfigEvent;
use Integrated\Bundle\ChannelBundle\Model\OauthConfigInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

class FacebookConfiguration implements OauthConfigInterface
{
    public function __construct(
        private readonly FacebookClient $client,
    )
    {
    }

    public function getName(): string
    {
        return FacebookConnector::NAME;
    }

    public function getForm(): string
    {
        return FacebookConfigType::class;
    }

    public function prepareAuthLink(ConfigEvent $event, OptionsInterface $options): ?string
    {
        if($options->has('token_secret')) {
            return null;
        }

        return $this->client->getAuthUrl();
    }

    public function handleCallback(ConfigEvent $event, OptionsInterface $options): bool
    {
        $code = $event->getRequest()->get('code');

        if(!$code) {
            return false;
        }

        try {
            $accessToken = $this->client->exchangeAccessToken($code);
        } catch (RequestException $exception) {
            return false;
        }

        $options['token_secret'] = $accessToken['access_token'];

        return true;
    }
}
