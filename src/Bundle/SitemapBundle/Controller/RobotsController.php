<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SitemapBundle\Controller;

use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

class RobotsController extends AbstractController
{
    private const CACHE_TTL = 86400;

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly SitemapCacheVersionManager $cacheVersionManager,
    ) {
    }

    public function index(Request $request): Response
    {
        $cacheKey = $this->buildResponseCacheKey('robots', $request, [
            (string) $this->cacheVersionManager->getChannelVersion($this->resolveChannelId($request)),
        ]);

        if ($response = $this->getCachedResponse($cacheKey, $request)) {
            return $response;
        }

        $response = $this->render('@IntegratedSitemap/robots/index.txt.twig');
        $generatedAt = new \DateTimeImmutable();
        $this->saveResponseToCache($cacheKey, $response, $generatedAt, self::CACHE_TTL);

        $response->setPublic();
        $response->setMaxAge(self::CACHE_TTL);
        $response->setSharedMaxAge(self::CACHE_TTL);
        $response->headers->addCacheControlDirective('stale-while-revalidate', (string) self::CACHE_TTL);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, '1');
        $response->setLastModified($generatedAt);
        $response->setEtag(sha1((string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    private function resolveChannelId(Request $request): string
    {
        $rawChannel = $request->attributes->get('_channel');
        if (\is_string($rawChannel) && '' !== trim($rawChannel)) {
            return trim($rawChannel);
        }

        return $request->getHost();
    }

    private function buildResponseCacheKey(string $scope, Request $request, array $parts): string
    {
        return 'integrated_sitemap_response_'.sha1(implode('|', [
            $scope,
            $request->getSchemeAndHttpHost(),
            $request->getBaseUrl(),
            $request->getLocale(),
            ...$parts,
        ]));
    }

    private function getCachedResponse(string $cacheKey, Request $request): ?Response
    {
        $item = $this->cache->getItem($cacheKey);
        if (!$item->isHit()) {
            return null;
        }

        $payload = $item->get();
        if (!\is_array($payload) || !isset($payload['content'], $payload['generated_at'])) {
            $this->cache->deleteItem($cacheKey);

            return null;
        }

        $response = new Response((string) $payload['content']);
        if (isset($payload['content_type']) && \is_string($payload['content_type']) && '' !== $payload['content_type']) {
            $response->headers->set('Content-Type', $payload['content_type']);
        }

        $response->setPublic();
        $response->setMaxAge(self::CACHE_TTL);
        $response->setSharedMaxAge(self::CACHE_TTL);
        $response->headers->addCacheControlDirective('stale-while-revalidate', (string) self::CACHE_TTL);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, '1');
        $response->setLastModified((new \DateTimeImmutable())->setTimestamp(max(1, (int) $payload['generated_at'])));
        $response->setEtag(sha1((string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    private function saveResponseToCache(string $cacheKey, Response $response, \DateTimeImmutable $generatedAt, int $ttl): void
    {
        $item = $this->cache->getItem($cacheKey);
        $item->set([
            'content' => (string) $response->getContent(),
            'content_type' => $response->headers->get('Content-Type'),
            'generated_at' => $generatedAt->getTimestamp(),
        ]);
        $item->expiresAfter($ttl);
        $this->cache->save($item);
    }
}
