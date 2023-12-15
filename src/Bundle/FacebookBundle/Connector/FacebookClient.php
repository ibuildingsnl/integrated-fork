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
                'pages_manage_engagement',
                'pages_manage_posts',
                'pages_read_engagement',
                'pages_read_user_engagement',
                'pages_show_list',
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

    public function getUserId(string $bearerToken): string {
        $response = $this->client->get("{$this->baseUrl}/me?fields=id", [
            'headers' => [
                'Authorization' => "Bearer {$bearerToken}",
            ],
        ]);

        return json_decode($response->getBody()->getContents())->id;
    }

    public function getPages(string $bearerToken): array {
        $userId = $this->getUserId($bearerToken);

        $response = $this->client->get("{$this->baseUrl}/{$userId}/accounts", [
            'headers' => [
                'Authorization' => "Bearer {$bearerToken}"
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function postToPage(string $bearerToken, string $pageId, string $title, string $message, ?string $link): string {
        $response = $this->client->post("{$this->baseUrl}/{$pageId}/feed", [
            'headers' => [
                'Authorization' => "Bearer {$bearerToken}",
            ],
            'json' => [
                'message' => "{$title}\n\n{$message}",
                'link' => $link,
                'published' => true,
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true)['id'];
    }

    private function assocArrayToQueryString(array $array): string {
        $query = [];

        foreach ($array as $key => $value) {
            $query[] = "{$key}={$value}";
        }

        return implode('&', $query);
    }
}
