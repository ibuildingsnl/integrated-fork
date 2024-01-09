<?php

namespace Integrated\Bundle\InstagramBundle\Connector;

use GuzzleHttp\Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class InstagramClient
{
    public function __construct(
        private readonly Client         $client,
        private readonly string         $appId,
        private readonly string         $secret,
        private readonly string         $loginUrl,
        private readonly string         $baseUrl,
        private readonly string         $redirectUrl,
        private readonly CacheInterface $cache,
    )
    {
    }

    public function getAuthUrl(): string
    {
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
                'pages_show_list',
                'instagram_basic',
                'instagram_content_publish',
                'business_management',
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
    public function exchangeAccessToken(string $code): array
    {
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

    public function clearPagesCache(string $userToken)
    {
        $this->cache->delete("{$userToken}-ig-pages");
    }

    public function clearInstagramAccountCache(string $pageToken)
    {
        $this->cache->delete("{$pageToken}-ig-account");
    }

    public function getPages(string $userToken): array
    {
        $pages = $this->cache->get("{$userToken}-ig-pages", function (ItemInterface $item) use ($userToken) {
            $item->expiresAfter(3600); // 1 hour

            $response = $this->client->get("{$this->baseUrl}/me/accounts", [
                'headers' => [
                    'Authorization' => "Bearer {$userToken}"
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        });

        return $pages;
    }

    public function getInstagramAccount(string $pageToken, string $pageId)
    {
        $accountId = $this->cache->get("{$pageToken}-ig-account", function (ItemInterface $item) use ($pageToken, $pageId) {
            $item->expiresAfter(3600); // 1 hour

            $response = $this->client->get("{$this->baseUrl}/{$pageId}?fields=instagram_business_account", [
                'headers' => [
                    'Authorization' => "Bearer {$pageToken}",
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true)['instagram_business_account']['id'];
        });

        return $accountId;
    }

    public function postToPage(string $pageToken, string $igUserId, string|array $content, ?string $caption): string
    {
        dump('Caption: ' . $caption);

        $images = is_array($content) ? $content : [$content];
        $containerIds = [];

        foreach ($images as $key => $image) {
            dump("Image {$key}: {$image}");
            $json = [
                'image_url' => 'https://via.placeholder.com/400x400',
                'caption' => $caption,
            ];

            if(count($images) > 1) {
                $json['is_carousel_item'] = true;
            }


            $response = $this->client->post("{$this->baseUrl}/{$igUserId}/media", [
                'headers' => [
                    'Authorization' => "Bearer {$pageToken}",
                ],
                'json' => $json
            ]);

            $containerId = json_decode($response->getBody()->getContents(), true)['id'];
            $containerIds[] = $containerId;
        }

        $commaSepContainers = implode(',', $containerIds);
        $response = $this->client->post("{$this->baseUrl}/{$igUserId}/media", [
            'headers' => [
                'Authorization' => "Bearer {$pageToken}",
            ],
            'json' => [
                'media_type' => 'CAROUSEL',
                'children' => $commaSepContainers,
                'caption' => $caption,
            ]
        ]);

        $carouselContainerId = json_decode($response->getBody()->getContents(), true)['id'];

        $response = $this->client->post("{$this->baseUrl}/{$igUserId}/media_publish", [
            'headers' => [
                'Authorization' => "Bearer {$pageToken}",
            ],
            'json' => [
                'creation_id' => $carouselContainerId,
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
        if (!$pages) {
            $pages = $this->getPages($userToken)['data'];
        }

        foreach ($pages as $page) {
            if ($page['id'] === $pageId) {
                return $page['access_token'];
            }
        }

        return null;
    }

    private function assocArrayToQueryString(array $array): string
    {
        $query = [];

        foreach ($array as $key => $value) {
            $query[] = "{$key}={$value}";
        }

        return implode('&', $query);
    }
}
