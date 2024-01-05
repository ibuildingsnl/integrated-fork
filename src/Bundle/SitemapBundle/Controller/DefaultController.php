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
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Services\ContentTypeInformation;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends AbstractController
{
    private DocumentManager $manager;
    private ChannelContextInterface $context;
    private ContentTypeInformation $contentTypeInformation;

    public function __construct(
        DocumentManager $manager,
        ChannelContextInterface $context,
        ContentTypeInformation $contentTypeInformation
    ) {
        $this->manager = $manager;
        $this->context = $context;
        $this->contentTypeInformation = $contentTypeInformation;
    }

    public function index(): Response
    {
        $channel = $this->context->getChannel();

        if (!$channel) {
            throw new NotFoundHttpException('No channel found');
        }

        $now = new \DateTime();

        $queryBuilder = $this->manager->createQueryBuilder(Content::class);
        $count = $queryBuilder
            ->count()
            ->field('channels.$id')->equals($channel->getId())
            ->field('disabled')->equals(false)
            ->field('publishTime.startDate')->lte($now)
            ->field('publishTime.endDate')->gte($now)
            ->field('contentType')->in($this->contentTypeInformation->getPublishingAllowedContentTypes($channel->getId()))
            ->addOr($queryBuilder->expr()->field('primaryChannel.$id')->equals($channel->getId()))
            ->addOr($queryBuilder->expr()->field('primaryChannel')->exists(false))
            ->getQuery()
            ->execute();

        if (!$count) {
            throw new NotFoundHttpException();
        }

        return $this->render('@IntegratedSitemapBundle/Default/index.html.twig', [
            'count' => min(ceil((int) $count / 50000), 50000),
        ]);
    }

    public function list(int $page): Response
    {
        $channel = $this->context->getChannel();

        if (!$channel) {
            throw new NotFoundHttpException('No channel found');
        }

        if ($page != min(max($page, 1), 50000)) {
            throw new NotFoundHttpException();
        }

        $now = new \DateTime();

        $queryBuilder = $this->manager->createQueryBuilder(Content::class);

        $documents = $queryBuilder
            ->select('contentType', 'slug', 'createdAt', 'class')
            ->field('channels.$id')->equals($channel->getId())
            ->field('disabled')->equals(false)
            ->field('publishTime.startDate')->lte($now)
            ->field('publishTime.endDate')->gte($now)
            ->field('contentType')->in($this->contentTypeInformation->getPublishingAllowedContentTypes($channel->getId()))
            ->addOr($queryBuilder->expr()->field('primaryChannel.$id')->equals($channel->getId()))
            ->addOr($queryBuilder->expr()->field('primaryChannel')->exists(false))
            ->sort('_id')
            ->skip(--$page * 50000)
            ->limit(50000)
            ->getQuery()
            ->getIterator();

        return $this->render('@IntegratedSitemapBundle/Default/list.html.twig', [
            'documents' => $documents,
        ]);
    }
}
