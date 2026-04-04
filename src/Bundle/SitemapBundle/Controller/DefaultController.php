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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Services\ContentTypeInformation;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\SitemapBundle\Service\SitemapCacheVersionManager;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

class DefaultController extends AbstractController
{
    private const PAGE_SIZE = 50000;
    private const MAX_PAGE = 50000;
    private const CACHE_TTL = 86400;
    private const EXCLUDED_TYPES = [
        'file',
        'image',
        'import_file',
        'comment',
        'taxonomy',
        'tag',
    ];

    private DocumentManager $manager;
    private ChannelContextInterface $context;
    private ContentTypeInformation $contentTypeInformation;
    private CacheItemPoolInterface $cache;
    private SitemapCacheVersionManager $cacheVersionManager;

    public function __construct(
        DocumentManager $manager,
        ChannelContextInterface $context,
        ContentTypeInformation $contentTypeInformation,
        CacheItemPoolInterface $cache,
        SitemapCacheVersionManager $cacheVersionManager,
    ) {
        $this->manager = $manager;
        $this->context = $context;
        $this->contentTypeInformation = $contentTypeInformation;
        $this->cache = $cache;
        $this->cacheVersionManager = $cacheVersionManager;
    }

    public function index(Request $request): Response
    {
        $channel = $this->getChannelOr404();
        $channelId = (string) $channel->getId();
        $cacheKey = $this->buildResponseCacheKey('index', $request, [
            $channelId,
            (string) $this->cacheVersionManager->getChannelContentVersion($channelId),
            (string) $this->cacheVersionManager->getChannelPagesVersion($channelId),
        ]);

        if ($response = $this->getCachedResponse($cacheKey, $request)) {
            return $response;
        }

        $now = new \DateTimeImmutable();
        $sections = $this->buildTypeSections($channelId, $now);
        $pagesCount = $this->getPagesSectionCount($channelId, $now);
        if (!$sections && !$pagesCount) {
            throw new NotFoundHttpException();
        }

        $response = $this->render('@IntegratedSitemap/default/index.xml.twig', [
            'sections' => $sections,
            'pagesCount' => $pagesCount,
            'generatedAt' => $now,
        ]);

        $this->saveResponseToCache($cacheKey, $response, $now, self::CACHE_TTL);

        return $this->withCacheHeaders($request, $response, $now);
    }

    public function list(Request $request, int $page): Response
    {
        $page = $this->getValidatedPage($page);
        $channel = $this->getChannelOr404();
        $channelId = (string) $channel->getId();
        $cacheKey = $this->buildResponseCacheKey('list', $request, [
            $channelId,
            (string) $this->cacheVersionManager->getChannelContentVersion($channelId),
            (string) $page,
        ]);

        if ($response = $this->getCachedResponse($cacheKey, $request)) {
            return $response;
        }

        $now = new \DateTimeImmutable();

        $documents = $this->createPublishedQueryBuilder($channelId, $now)
            ->select('contentType', 'slug', 'createdAt', 'class')
            ->sort(['publishTime.startDate' => 'desc', '_id' => 'asc'])
            ->skip(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->getQuery()
            ->getIterator();

        $response = $this->render('@IntegratedSitemap/default/list.xml.twig', [
            'documents' => $documents,
        ]);

        $this->saveResponseToCache($cacheKey, $response, $now, self::CACHE_TTL);

        return $this->withCacheHeaders($request, $response, $now);
    }

    public function listByType(Request $request, string $type, int $page): Response
    {
        $page = $this->getValidatedPage($page);
        $channel = $this->getChannelOr404();
        $channelId = (string) $channel->getId();
        $cacheKey = $this->buildResponseCacheKey('list_by_type', $request, [
            $channelId,
            $type,
            (string) $this->cacheVersionManager->getChannelVersion($channelId),
            (string) $this->cacheVersionManager->getChannelTypeVersion($channelId, $type),
            (string) $page,
        ]);

        if ($response = $this->getCachedResponse($cacheKey, $request)) {
            return $response;
        }

        $this->assertAllowedContentType($type, $channelId);
        $now = new \DateTimeImmutable();

        $documents = $this->createPublishedQueryBuilder($channelId, $now)
            ->field('contentType')->equals($type)
            ->select('contentType', 'slug', 'createdAt', 'updatedAt', 'publishTime')
            ->sort(['publishTime.startDate' => 'desc', '_id' => 'asc'])
            ->skip(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->getQuery()
            ->getIterator();

        $response = $this->render('@IntegratedSitemap/default/list.xml.twig', [
            'documents' => $documents,
        ]);

        $this->saveResponseToCache($cacheKey, $response, $now, self::CACHE_TTL);

        return $this->withCacheHeaders($request, $response, $now);
    }

    public function listPages(Request $request, int $page): Response
    {
        $page = $this->getValidatedPage($page);
        $channel = $this->getChannelOr404();
        $channelId = (string) $channel->getId();
        $cacheKey = $this->buildResponseCacheKey('list_pages', $request, [
            $channelId,
            (string) $this->cacheVersionManager->getChannelVersion($channelId),
            (string) $this->cacheVersionManager->getChannelPagesVersion($channelId),
            (string) $page,
        ]);

        if ($response = $this->getCachedResponse($cacheKey, $request)) {
            return $response;
        }

        $now = new \DateTimeImmutable();

        $documents = $this->createPageQueryBuilder($channelId, $now)
            ->select('path', 'createdAt', 'updatedAt')
            ->sort('path')
            ->skip(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->getQuery()
            ->getIterator();

        $response = $this->render('@IntegratedSitemap/default/list.xml.twig', [
            'documents' => $documents,
        ]);

        $this->saveResponseToCache($cacheKey, $response, $now, self::CACHE_TTL);

        return $this->withCacheHeaders($request, $response, $now);
    }

    private function getChannelOr404(): ChannelInterface
    {
        $channel = $this->context->getChannel();
        if (!$channel) {
            throw new NotFoundHttpException('No channel found');
        }

        return $channel;
    }

    private function getValidatedPage(int $page): int
    {
        if ($page !== min(max($page, 1), self::MAX_PAGE)) {
            throw new NotFoundHttpException();
        }

        return $page;
    }

    private function createPublishedQueryBuilder(string $channelId, \DateTimeInterface $now): Builder
    {
        $queryBuilder = $this->manager->createQueryBuilder(Content::class);

        return $queryBuilder
            ->field('channels.$id')->equals($channelId)
            ->field('disabled')->equals(false)
            ->field('slug')->exists(true)
            ->field('slug')->notEqual('')
            ->field('seoMetadata.noindex')->notEqual(true)
            ->field('publishTime.startDate')->lte($now)
            ->field('publishTime.endDate')->gte($now)
            ->field('contentType')->in($this->getIndexableContentTypes($channelId))
            ->addOr($queryBuilder->expr()->field('primaryChannel.$id')->equals($channelId))
            ->addOr($queryBuilder->expr()->field('primaryChannel')->exists(false));
    }

    /**
     * @return list<array{type: string, count: int}>
     */
    private function buildTypeSections(string $channelId, \DateTimeInterface $now): array
    {
        $sections = [];
        foreach ($this->getIndexableContentTypes($channelId) as $type) {
            $count = (clone $this->createPublishedQueryBuilder($channelId, $now))
                ->field('contentType')->equals($type)
                ->count()
                ->getQuery()
                ->execute();

            $countValue = $this->normalizeCount($count);
            if ($countValue <= 0) {
                continue;
            }

            $sections[] = [
                'type' => $type,
                'count' => min((int) ceil($countValue / self::PAGE_SIZE), self::MAX_PAGE),
            ];
        }

        return $sections;
    }

    private function getPagesSectionCount(string $channelId, \DateTimeInterface $now): int
    {
        $count = $this->createPageQueryBuilder($channelId, $now)
            ->count()
            ->getQuery()
            ->execute();

        return min((int) ceil($this->normalizeCount($count) / self::PAGE_SIZE), self::MAX_PAGE);
    }

    private function assertAllowedContentType(string $type, string $channelId): void
    {
        if (!\in_array($type, $this->getIndexableContentTypes($channelId), true)) {
            throw new NotFoundHttpException();
        }
    }

    /** @return list<string> */
    private function getIndexableContentTypes(string $channelId): array
    {
        return $this->contentTypeInformation->getSitemapAllowedContentTypes($channelId, self::EXCLUDED_TYPES);
    }

    private function withCacheHeaders(Request $request, Response $response, \DateTimeInterface $generatedAt): Response
    {
        $response->setPublic();
        $response->setMaxAge(self::CACHE_TTL);
        $response->setSharedMaxAge(self::CACHE_TTL);
        $response->headers->addCacheControlDirective('stale-while-revalidate', (string) self::CACHE_TTL);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, '1');
        $response->setLastModified(\DateTimeImmutable::createFromInterface($generatedAt));
        $response->setEtag(sha1((string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
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

        $generatedAt = (new \DateTimeImmutable())->setTimestamp(max(1, (int) $payload['generated_at']));

        return $this->withCacheHeaders($request, $response, $generatedAt);
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

    private function normalizeCount(mixed $value): int
    {
        if (\is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return 0;
    }

    private function createPageQueryBuilder(string $channelId, \DateTimeInterface $now): Builder
    {
        $queryBuilder = $this->manager->createQueryBuilder(Page::class);

        $queryBuilder
            ->field('channel.$id')->equals($channelId)
            ->field('disabled')->equals(false)
            ->field('path')->exists(true)
            ->field('path')->notEqual('')
            ->field('hideFromSitemap')->notEqual(true);

        $publishExpr = $queryBuilder->expr();
        $publishExpr->addOr($queryBuilder->expr()->field('publishAt')->equals(null));
        $publishExpr->addOr($queryBuilder->expr()->field('publishAt')->lte($now));
        $queryBuilder->addAnd($publishExpr);

        $expireExpr = $queryBuilder->expr();
        $expireExpr->addOr($queryBuilder->expr()->field('expireAt')->equals(null));
        $expireExpr->addOr($queryBuilder->expr()->field('expireAt')->gt($now));
        $queryBuilder->addAnd($expireExpr);

        return $queryBuilder;
    }
}
