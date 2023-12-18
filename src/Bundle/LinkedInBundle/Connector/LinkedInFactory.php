<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use League\OAuth2\Client\Provider\LinkedIn;

// @TODO
final class LinkedInFactory
{
    public function __construct(
        private readonly string $key,
        private readonly string $secret,
    ) {
    }

    public function createClient(?string $token = null): LinkedIn
    {
        $redirectUrl = 'https://integrated.localhost.e-active.nl/admin/connector/config/external/return';

        return new LinkedIn([
            'clientId' => $this->key,
            'clientSecret' => $this->secret,
            'redirectUri' => $redirectUrl,
        ]);
    }

    public function getHeaders(): array
    {
        return [
            'LinkedIn-Version' => '202309',
            'X-Restli-Protocol-Version' => '2.0.0',
            'Cookie' => 'lidc="b=TB74:s=T:r=T:a=T:p=T:g=3873:u=246:x=1:i=1696253933:t=1696335588:v=2:' .
                'sig=AQEXD88VnyHqy_viJAtYHJ8KTJUl3teJ"; lidc="b=TB74:s=T:r=T:a=T:p=T:g=3878:u=248:x=1:' .
                'i=1696404063:t=1696487562:v=2:sig=AQGWyk189Wd1cZaJcPTFnoDJPNX8moEn"; ' .
                'bcookie="v=2&23a437ae-3da8-47c3-8018-cbe1c396531b"',
        ];
    }
}
