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
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends AbstractController
{
    private const PAGE_SIZE = 50000;
    private const MAX_PAGE = 50000;
    private const CACHE_TTL = 900;
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

    public function __construct(
        DocumentManager $manager,
        ChannelContextInterface $context,
        ContentTypeInformation $contentTypeInformation,
    ) {
        $this->manager = $manager;
        $this->context = $context;
        $this->contentTypeInformation = $contentTypeInformation;
    }

    public function index(Request $request): Response
    {
        $channel = $this->getChannelOr404();
        $now = new \DateTimeImmutable();
        $channelId = (string) $channel->getId();
        $sections = $this->buildTypeSections($channelId, $now);
        $pagesCount = $this->getPagesSectionCount($channelId);
        if (!$sections && !$pagesCount) {
            throw new NotFoundHttpException();
        }

        $response = $this->render('@IntegratedSitemap/default/index.xml.twig', [
            'sections' => $sections,
            'pagesCount' => $pagesCount,
            'generatedAt' => $now,
        ]);

        return $this->withCacheHeaders($request, $response, $now);
    }

    public function list(Request $request, int $page): Response
    {
        $page = $this->getValidatedPage($page);
        $channel = $this->getChannelOr404();
        $now = new \DateTimeImmutable();
        $channelId = (string) $channel->getId();

        $documents = $this->createPublishedQueryBuilder($channelId, $now)
            ->select('contentType', 'slug', 'createdAt', 'class')
            ->sort('_id')
            ->skip(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->getQuery()
            ->getIterator();

        $response = $this->render('@IntegratedSitemap/default/list.xml.twig', [
            'documents' => $documents,
        ]);

        return $this->withCacheHeaders($request, $response, $now);
    }

    public function listByType(Request $request, string $type, int $page): Response
    {
        $page = $this->getValidatedPage($page);
        $channel = $this->getChannelOr404();
        $channelId = (string) $channel->getId();
        $this->assertAllowedContentType($type, $channelId);
        $now = new \DateTimeImmutable();

        $documents = $this->createPublishedQueryBuilder($channelId, $now)
            ->field('contentType')->equals($type)
            ->select('contentType', 'slug', 'createdAt', 'updatedAt', 'publishTime')
            ->sort('_id')
            ->skip(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->getQuery()
            ->getIterator();

        $response = $this->render('@IntegratedSitemap/default/list.xml.twig', [
            'documents' => $documents,
        ]);

        return $this->withCacheHeaders($request, $response, $now);
    }

    public function listPages(Request $request, int $page): Response
    {
        $page = $this->getValidatedPage($page);
        $channel = $this->getChannelOr404();
        $now = new \DateTimeImmutable();
        $channelId = (string) $channel->getId();

        $documents = $this->manager->createQueryBuilder(Page::class)
            ->field('channel.$id')->equals($channelId)
            ->field('disabled')->equals(false)
            ->field('path')->exists(true)
            ->field('path')->notEqual('')
            ->select('path', 'createdAt', 'updatedAt')
            ->sort('path')
            ->skip(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->getQuery()
            ->getIterator();

        $response = $this->render('@IntegratedSitemap/default/list.xml.twig', [
            'documents' => $documents,
        ]);

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

    private function getPagesSectionCount(string $channelId): int
    {
        $count = $this->manager->createQueryBuilder(Page::class)
            ->field('channel.$id')->equals($channelId)
            ->field('disabled')->equals(false)
            ->field('path')->exists(true)
            ->field('path')->notEqual('')
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
        $allowed = $this->contentTypeInformation->getPublishingAllowedContentTypes($channelId);

        return array_values(array_filter(
            $allowed,
            static fn (string $type): bool => !\in_array(strtolower($type), self::EXCLUDED_TYPES, true)
        ));
    }

    private function withCacheHeaders(Request $request, Response $response, \DateTimeInterface $generatedAt): Response
    {
        $response->setPublic();
        $response->setMaxAge(self::CACHE_TTL);
        $response->setSharedMaxAge(self::CACHE_TTL);
        $response->headers->addCacheControlDirective('stale-while-revalidate', (string) self::CACHE_TTL);
        $response->setLastModified(\DateTimeImmutable::createFromInterface($generatedAt));
        $response->setEtag(sha1((string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
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
}
