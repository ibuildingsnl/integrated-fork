<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

//use Abraham\LinkedInOAuth\LinkedInOAuthException;
use Integrated\Bundle\ChannelBundle\Event\ConfigEvent;
use Integrated\Bundle\ChannelBundle\Model\ConfigurationException;
use Integrated\Bundle\ChannelBundle\Model\OauthConfigInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LinkedInConfiguration implements OauthConfigInterface
{
    public function __construct(
        private readonly LinkedInFactory $factory,
        private readonly UrlGeneratorInterface $generator,
    ) {}

    public function getName(): string
    {
        return LinkedInConnector::NAME;
    }

    public function getForm(): string
    {
        return LinkedInConfigType::class;
    }

    public function prepareAuthLink(ConfigEvent $event, OptionsInterface $options): ?string
    {
        if ($options->has('token')) {
            return null;
        }

        $client = $this->factory->createClient();

        $options = [
            'state' => '12345' . random_bytes(5),
            'scope' => 'w_organization_social' // array or string
        ];

        if (!isset($_GET['code'])) {
            // If we don't have an authorization code then get one
            $authUrl = $client->getAuthorizationUrl($options);
            $_SESSION['oauth2state'] = $client->getState();
            header('Location: ' . $authUrl);
            exit;
        }

        try {
            $response = $client->oauth(
                'oauth/request_token',
                ['oauth_callback' => $this->generator->generate(
                    'integrated_channel_config_external_return',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )],
            );
        } catch (\Exception $e) {
            throw ConfigurationException::encountered($e);
        }

        return $client->url('oauth/authorize', ['oauth_token' => $response['oauth_token']]);
    }

    public function handleCallback(ConfigEvent $event, OptionsInterface $options): bool
    {
        $client = $this->factory->createClient();

        // Try to get an access token (using the authorization code grant)
        $token = $client->getAccessToken('authorization_code', [
            'code' => $event->getRequest()->get('code')
        ]);

        if (!$token) {
            return false;
        }

        try {
            $response = $this->factory->createClient($token->getToken());
        } catch (LinkedInOAuthException $e) {
            throw ConfigurationException::encountered($e);
        }

        $options
            ->set('token', $token)
            ->remove('request_token');

        return true;
    }
}
