<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Embed\Embed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OEmbedController extends AbstractController
{
    public function __construct(
        private readonly string $fbAppId,
        private readonly string $fbSecret,
        private readonly string $xAppId,
        private readonly string $xSecret,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function oEmbed(Request $request): JsonResponse
    {
        $url = $this->extractRequestedUrl($request);

        if ($url === null) {
            return new JsonResponse(['msg' => 'No URL specified'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $info = $this->fetchEmbedInfo($url);
        } catch (\Throwable $exception) {
            return new JsonResponse(['msg' => 'Unable to fetch embed data from provider.'], Response::HTTP_BAD_GATEWAY);
        }

        $code = $this->getNestedProperty($info, 'code', 'html');

        if ($code === null && $this->isFacebookUrl($url)) {
            $code = $this->buildFacebookFallbackCode($url);
        }

        if ($code === null) {
            $oEmbedDebug = $this->extractOEmbedDebug($info);
            $permissionFailure = $this->detectMetaOEmbedPermissionFailure($info, $url, $oEmbedDebug['data']);

            $payload = [
                'msg' => 'No embeddable content returned for this URL.',
                'debug' => [
                    'requested_url' => $url,
                    'provider_name' => $this->getProperty($info, 'providerName'),
                    'provider_url' => $this->getProperty($info, 'providerUrl'),
                    'resolved_url' => $this->getProperty($info, 'url'),
                    'has_code' => $this->getProperty($info, 'code') !== null,
                    'title' => $this->getProperty($info, 'title'),
                    'description' => $this->getProperty($info, 'description'),
                    'image' => $this->getProperty($info, 'image'),
                    'author_name' => $this->getProperty($info, 'authorName'),
                    'oembed_endpoint' => $oEmbedDebug['endpoint'],
                    'oembed_data' => $oEmbedDebug['data'],
                ],
            ];

            if ($permissionFailure !== null) {
                $payload['msg'] = \sprintf(
                    '%s oEmbed is not available for this app. Meta oEmbed Read approval is required.',
                    $permissionFailure
                );
                $payload['reason'] = 'meta_oembed_permission_required';
            }

            return new JsonResponse($payload, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'title' => $this->getProperty($info, 'title'),
            'description' => $this->getProperty($info, 'description'),
            'url' => $this->getProperty($info, 'url') ?? $url,
            'keywords' => $this->getProperty($info, 'keywords'),
            'image' => $this->getProperty($info, 'image'),
            'code' => $code,
            'width' => $this->getNestedProperty($info, 'code', 'width'),
            'height' => $this->getNestedProperty($info, 'code', 'height'),
            'ratio' => $this->getNestedProperty($info, 'code', 'ratio'),
            'author_name' => $this->getProperty($info, 'authorName'),
            'author_url' => $this->getProperty($info, 'authorUrl'),
            'provider_name' => $this->getProperty($info, 'providerName'),
            'provider_url' => $this->getProperty($info, 'providerUrl'),
            'provider_icon' => $this->getProperty($info, 'icon'),
            'favicon' => $this->getProperty($info, 'favicon'),
            'published_date' => $this->getProperty($info, 'publishedTime'),
            'license' => $this->getProperty($info, 'license'),
            'feeds' => $this->getProperty($info, 'feeds'),
        ]);
    }

    private function extractRequestedUrl(Request $request): ?string
    {
        $value = $request->query->get('url');

        if (!\is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    private function fetchEmbedInfo(string $url): object
    {
        $embed = new Embed();
        $embed->setSettings(
            [
                'facebook:token' => $this->fbAppId.'|'.$this->fbSecret,
                'instagram:token' => $this->fbAppId.'|'.$this->fbSecret,
                'twitter:token' => $this->xAppId.'|'.$this->xSecret,
            ]
        );

        return $embed->get($url);
    }

    private function isFacebookUrl(string $url): bool
    {
        return (bool) preg_match('#^https?://(www\.)?facebook\.com/#i', $url);
    }

    private function buildFacebookFallbackCode(string $url): string
    {
        $escapedUrl = htmlspecialchars($url, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');

        return \sprintf(
            '<div class="fb-post" data-href="%s" data-show-text="true"></div>',
            $escapedUrl
        );
    }

    private function getProperty(object $info, string $property): mixed
    {
        return $info->{$property} ?? null;
    }

    private function getNestedProperty(object $info, string $property, string $nestedProperty): mixed
    {
        $value = $this->getProperty($info, $property);

        if (!\is_object($value)) {
            return null;
        }

        return $value->{$nestedProperty} ?? null;
    }

    /**
     * @return array{endpoint: ?string, data: array<mixed>}
     */
    private function extractOEmbedDebug(object $info): array
    {
        if (!method_exists($info, 'getOEmbed')) {
            return [
                'endpoint' => null,
                'data' => [],
            ];
        }

        $oEmbed = $info->getOEmbed();

        if (!\is_object($oEmbed)) {
            return [
                'endpoint' => null,
                'data' => [],
            ];
        }

        $endpoint = method_exists($oEmbed, 'getEndpoint') ? $oEmbed->getEndpoint() : null;
        $data = method_exists($oEmbed, 'all') ? $oEmbed->all() : [];

        return [
            'endpoint' => $endpoint !== null ? $this->sanitizeDebugEndpoint((string) $endpoint) : null,
            'data' => \is_array($data) ? $this->sanitizeDebugData($data) : [],
        ];
    }

    /**
     * @param array<string, mixed> $oEmbedData
     */
    private function detectMetaOEmbedPermissionFailure(object $info, string $requestedUrl, array $oEmbedData): ?string
    {
        $providerLabel = $this->resolveMetaProviderLabel($info, $requestedUrl);

        if ($providerLabel === null) {
            return null;
        }

        $error = $oEmbedData['error'] ?? null;

        if (!\is_array($error)) {
            return null;
        }

        $errorCode = $error['code'] ?? null;
        $errorMessage = $error['message'] ?? null;

        if ($errorCode === 10) {
            return $providerLabel;
        }

        if (\is_string($errorMessage) && stripos($errorMessage, 'Meta oEmbed Read') !== false) {
            return $providerLabel;
        }

        return null;
    }

    private function resolveMetaProviderLabel(object $info, string $requestedUrl): ?string
    {
        $providerName = $this->getProperty($info, 'providerName');

        if (\is_string($providerName)) {
            $normalized = strtolower($providerName);

            if ($normalized === 'facebook') {
                return 'Facebook';
            }

            if ($normalized === 'instagram') {
                return 'Instagram';
            }
        }

        $resolvedUrl = $this->getProperty($info, 'url');
        $candidateUrls = [$requestedUrl];

        if (\is_string($resolvedUrl) && $resolvedUrl !== '') {
            $candidateUrls[] = $resolvedUrl;
        }

        foreach ($candidateUrls as $candidateUrl) {
            if (preg_match('#^https?://(www\.)?instagram\.com/#i', $candidateUrl)) {
                return 'Instagram';
            }

            if (preg_match('#^https?://(www\.)?facebook\.com/#i', $candidateUrl)) {
                return 'Facebook';
            }
        }

        return null;
    }

    private function sanitizeDebugEndpoint(string $endpoint): string
    {
        $parts = parse_url($endpoint);

        if ($parts === false || !isset($parts['query'])) {
            return $endpoint;
        }

        parse_str($parts['query'], $query);
        unset($query['access_token']);

        $sanitizedQuery = http_build_query($query);

        if ($sanitizedQuery === '') {
            return preg_replace('/\?.*$/', '', $endpoint) ?? $endpoint;
        }

        return preg_replace('/\?.*$/', '?'.$sanitizedQuery, $endpoint) ?? $endpoint;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function sanitizeDebugData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($key === 'access_token') {
                continue;
            }

            if (\is_array($value)) {
                $sanitized[$key] = $this->sanitizeDebugData($value);
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
}
