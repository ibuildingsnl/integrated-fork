<?php

namespace Integrated\Bundle\FacebookBundle\Connector;

use GuzzleHttp\Client;

class FacebookClient
{
    public function __construct(
        private readonly Client $client,
        private readonly string $appId,
        private readonly string $secret,
        private readonly string $loginUrl,
        private readonly string $baseUrl,
        private readonly string $redirectUrl,
    )
    {
    }

    public function getAuthUrl(): string {
        // Information about parameters
        // https://developers.facebook.com/docs/facebook-login/guides/advanced/manual-flow/#login
        $options = [
            'client_id' => $this->appId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'scope' => implode(',', [
                'pages_manage_posts',
                'pages_show_list',
                'pages_read_engagement'
            ]),
        ];

        $url = "{$this->loginUrl}/dialog/oauth?";

        return $url . $this->assocArrayToQueryString($options);
    }

    /**
     * Exchange the code received from the Facebook login flow for an Access Token.
     *
     * @param string $code
     * @return array
     * @see https://developers.facebook.com/docs/facebook-login/guides/advanced/manual-flow/#exchangecode
     */
    public function exchangeAccessToken(string $code): array {
        $options = [
            'client_id' => $this->appId,
            'redirect_uri' => $this->redirectUrl,
            'client_secret' => $this->secret,
            'code' => $code,
        ];

        $response = $this->client->get("{$this->baseUrl}/oauth/access_token?{$this->assocArrayToQueryString($options)}");
        $data = json_decode($response->getBody()->getContents(), true);

        return $data;
    }

    private function assocArrayToQueryString(array $array): string {
        $query = [];

        foreach ($array as $key => $value) {
            $query[] = "{$key}={$value}";
        }

        return implode('&', $query);
    }
}
