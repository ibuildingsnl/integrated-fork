<?php

namespace Integrated\Bundle\XTwitterBundle\Connector;

use Abraham\TwitterOAuth\TwitterOAuth;

final class TwitterFactory
{
    public function __construct(
       private readonly string $key,
       private readonly string $secret,
    ) {}

    public function createClient(?string $token = null, ?string $secret = null): TwitterOAuth
    {
        $twitter = new TwitterOAuth($this->key, $this->secret, $token, $secret);
        $twitter->setApiVersion('2');
        $twitter->setDecodeJsonAsArray(true);
        return $twitter;
    }
}
