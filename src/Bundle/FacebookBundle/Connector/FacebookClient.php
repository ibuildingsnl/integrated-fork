<?php

namespace Integrated\Bundle\FacebookBundle\Connector;

use GuzzleHttp\Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FacebookClient
{
    public function __construct(
        private readonly Client $client,
        private readonly string $appId,
        private readonly string $secret,
        private readonly string $loginUrl,
        private readonly string $baseUrl,
        private readonly string $redirectUrl,
        private readonly CacheInterface $cache,
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
//                'pages_read_user_engagement',
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

    public function getUserId(string $userToken): string {
        $response = $this->client->get("{$this->baseUrl}/me?fields=id", [
            'headers' => [
                'Authorization' => "Bearer {$userToken}",
            ],
        ]);

        return json_decode($response->getBody()->getContents())->id;
    }

    public function getPages(string $userToken): array {
        $pages = $this->cache->get("{$userToken}-pages", function(ItemInterface $item) use ($userToken) {
            $item->expiresAfter(3600); // 1 hour
            $userId = $this->getUserId($userToken);

            $response = $this->client->get("{$this->baseUrl}/{$userId}/accounts", [
                'headers' => [
                    'Authorization' => "Bearer {$userToken}"
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        });

        return $pages;
    }

    public function postToPage(string $userToken, string $pageId, ?string $title, ?string $message, ?string $link): string {
        $title = $title ? $title . "\n\n" : '';
        $message = $message ? $message . "\n\n" :  '';

        $response = $this->client->post("{$this->baseUrl}/{$pageId}/feed", [
            'headers' => [
                'Authorization' => "Bearer {$userToken}",
            ],
            'json' => [
                'message' => "{$title}{$message}{$link}",
                'published' => true,
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true)['id'];
    }

    /**
     * @param string $userToken
     * @param string $pageId
     * @param array|null $pages If you already have done a call to fetch pages, you can reuse the result by passing the array here
     * @return string|null
     */
    public function getPageToken(string $userToken, string $pageId, ?array $pages = null): ?string
    {
        if(!$pages) {
            $pages = $this->getPages($userToken)['data'];
        }

        foreach ($pages as $page) {
            if($page['id'] === $pageId) {
                return $page['access_token'];
            }
        }

        return null;
    }

    private function assocArrayToQueryString(array $array): string {
        $query = [];

        foreach ($array as $key => $value) {
            $query[] = "{$key}={$value}";
        }

        return implode('&', $query);
    }
}
