<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use Abraham\TwitterOAuth\TwitterOAuth;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use League\OAuth2\Client\Provider\LinkedIn;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LinkedInFactory
{
    public function __construct(
        private readonly string $key,
        private readonly string $secret,
        private readonly UrlGeneratorInterface $generator,
    ) {}

    public function createClient(?string $token = null): LinkedIn
    {
        $redirectUrl =  'https://integrated.localhost.e-active.nl/admin/connector/config/external/return';

        return new LinkedIn([
            'clientId'          => $this->key,
            'clientSecret'      => $this->secret,
            'redirectUri'       => $redirectUrl
        ]);

        //doesnt work
//        $this->generator->generate(
//            'integrated_channel_config_external_return',
//            [],
//            UrlGeneratorInterface::ABSOLUTE_URL
//        )
//        ]
    }
}
