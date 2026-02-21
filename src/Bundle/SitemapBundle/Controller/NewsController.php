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
use Integrated\Bundle\ContentBundle\Document\Content\News;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsController extends AbstractController
{
    private DocumentManager $manager;
    private ChannelContextInterface $context;

    public function __construct(DocumentManager $manager, ChannelContextInterface $context)
    {
        $this->manager = $manager;
        $this->context = $context;
    }

    public function index(): Response
    {
        $channel = $this->context->getChannel();

        if (!$channel) {
            throw new NotFoundHttpException('No channel found');
        }

        $now = new \DateTime();

        $queryBuilder = $this->manager->createQueryBuilder(News::class);
        $documents = $queryBuilder
            ->select('contentType', 'slug', 'publishTime', 'title', 'relations')
            ->field('channels.$id')->equals($channel->getId())
            ->field('disabled')->equals(false)
            ->field('publishTime.startDate')->gte(new \DateTime('-2 days')) // Only the last 2 days for Google
            ->field('publishTime.startDate')->lte($now)
            ->field('publishTime.endDate')->gte($now)
            ->addOr($queryBuilder->expr()->field('primaryChannel.$id')->equals($channel->getId()))
            ->addOr($queryBuilder->expr()->field('primaryChannel')->exists(false))
            ->sort('createdAt', 'desc')
            ->limit(1000)
            ->getQuery()
            ->getIterator();

        return $this->render('@IntegratedSitemap/news/index.xml.twig', [
            'channel' => $channel,
            'locale' => $this->getParameter('kernel.default_locale'),
            'documents' => $documents,
        ]);
    }
}
