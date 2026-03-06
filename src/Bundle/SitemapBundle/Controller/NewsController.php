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
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsController extends AbstractController
{
    private const PAGE_SIZE = 1000;
    private const MAX_PAGE = 50000;
    private const NEWS_LOOKBACK = '-2 days';
    private const CACHE_TTL = 300;

    private DocumentManager $manager;
    private ChannelContextInterface $context;

    public function __construct(DocumentManager $manager, ChannelContextInterface $context)
    {
        $this->manager = $manager;
        $this->context = $context;
    }

    public function index(Request $request): Response
    {
        return $this->renderNewsPage($request, 1);
    }

    public function list(Request $request, int $page): Response
    {
        return $this->renderNewsPage($request, $page);
    }

    private function renderNewsPage(Request $request, int $page): Response
    {
        $page = $this->getValidatedPage($page);
        $channel = $this->getChannelOr404();
        $now = new \DateTimeImmutable();
        $channelId = (string) $channel->getId();

        $documents = $this->createPublishedNewsQueryBuilder($channelId, $now)
            ->select('contentType', 'slug', 'publishTime', 'title', 'createdAt', 'updatedAt')
            ->sort('createdAt', 'desc')
            ->skip(($page - 1) * self::PAGE_SIZE)
            ->limit(self::PAGE_SIZE)
            ->getQuery()
            ->getIterator();

        $response = $this->render('@IntegratedSitemap/news/index.xml.twig', [
            'channel' => $channel,
            'locale' => $this->getParameter('kernel.default_locale'),
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

    private function createPublishedNewsQueryBuilder(string $channelId, \DateTimeInterface $now): Builder
    {
        $queryBuilder = $this->manager->createQueryBuilder(News::class);

        return $queryBuilder
            ->field('channels.$id')->equals($channelId)
            ->field('disabled')->equals(false)
            ->field('slug')->exists(true)
            ->field('slug')->notEqual('')
            ->field('seoMetadata.noindex')->notEqual(true)
            // Google News sitemap only includes the most recent publication window.
            ->field('publishTime.startDate')->gte(new \DateTimeImmutable(self::NEWS_LOOKBACK))
            ->field('publishTime.startDate')->lte($now)
            ->field('publishTime.endDate')->gte($now)
            ->addOr($queryBuilder->expr()->field('primaryChannel.$id')->equals($channelId))
            ->addOr($queryBuilder->expr()->field('primaryChannel')->exists(false));
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
}
