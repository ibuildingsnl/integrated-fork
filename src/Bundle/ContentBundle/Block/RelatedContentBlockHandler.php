<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Integrated\Bundle\BlockBundle\Block\BlockHandler;
use Integrated\Bundle\ContentBundle\Document\Block\RelatedContentBlock;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Integrated\Common\Block\BlockInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Related content block handler.
 *
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class RelatedContentBlockHandler extends BlockHandler
{
    /**
     * Protect related-content blocks from pathological deep pagination requests
     * (commonly bot-driven query params such as "...-page=4000").
     */
    private const MAX_UNCAPPED_PAGE = 50;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var DocumentManager
     */
    private $dm;

    private ContentRepository $contentRepository;

    public function __construct(PaginatorInterface $paginator, RequestStack $requestStack, DocumentManager $dm, ContentRepository $contentRepository)
    {
        $this->paginator = $paginator;
        $this->requestStack = $requestStack;
        $this->dm = $dm;
        $this->contentRepository = $contentRepository;
    }

    public function execute(BlockInterface $block, array $options)
    {
        if (!$block instanceof RelatedContentBlock) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return null;
        }

        $pagination = $this->getPagination($block, $request);

        if (null === $pagination || !\count($pagination)) {
            return null;
        }

        return $this->render([
            'block' => $block,
            'pagination' => $pagination,
            'document' => $this->getDocument(),
            'options' => $options,
        ]);
    }

    /**
     * @return \Knp\Component\Pager\Pagination\PaginationInterface|null
     *
     * @throws \Exception
     */
    public function getPagination(RelatedContentBlock $block, Request $request, $document = null)
    {
        $target = $this->getQuery($block, $document);

        if (null === $target) {
            return null;
        }

        $pageParam = $block->getId().'-page';
        $itemsPerPage = $block->getItemsPerPage();
        $maxItems = $block->getMaxItems();
        $page = (int) $request->query->get($pageParam, 1);
        if ($page < 1) {
            $page = 1;
        }

        if ($maxItems > 0 && $itemsPerPage > 0) {
            $maxPageByCap = (int) ceil($maxItems / $itemsPerPage);
            if ($maxPageByCap > 0 && $page > $maxPageByCap) {
                $page = $maxPageByCap;
            }
        } elseif ($page > self::MAX_UNCAPPED_PAGE) {
            $page = 1;
        }

        if ($maxItems > 0 && $maxItems <= $itemsPerPage) {
            $page = 1;
            $target = $this->materializeSinglePageTarget($target, $maxItems);
        }

        $pagination = $this->paginator->paginate(
            $target,
            $page,
            $itemsPerPage,
            [
                'pageParameterName' => $pageParam,
                'maxItems' => $maxItems,
            ]
        );

        if ($maxItems > 0) {
            if ($maxItems <= $itemsPerPage) {
                $pagination->setCurrentPageNumber(1);
                $pagination->setTotalItemCount(\count($pagination));
            } else {
                $pagination->setTotalItemCount(min($maxItems, $pagination->getTotalItemCount()));
            }
        }

        return $pagination;
    }

    /**
     * @return Builder|null
     */
    protected function getQuery(RelatedContentBlock $block, $document)
    {
        if (!$document) {
            /** @var Article $document */
            $document = $this->getDocument();
        }
        if (!$document instanceof Content) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request === null || !$request->attributes->has('_channel')) {
            throw new \Exception('Channel not set');
        }

        switch ($block->getTypeBlock()) {
            case RelatedContentBlock::SHOW_LINKED_BY:
                $query = $this->getLinkedByQuery($document, $block);

                break;
            case RelatedContentBlock::SHOW_LINKED:
                if (!$linkedDocuments = $document->getReferencesByRelationId($block->getRelation()->getId())) {
                    return null;
                }

                $query = $this->contentRepository->getUsedBy($linkedDocuments, $block->getRelation(), $document);

                break;
            default:
                $query = $this->contentRepository->getUsedBy(new ArrayCollection([$document]), $block->getRelation());

                break;
        }

        if ($block->getContentTypes()) {
            $query->field('contentType')->in($block->getContentTypes());
        }

        $query->field('channels.$id')->equals($request->attributes->get('_channel'));

        if ($block->getSortBy() == 'linked' && $block->getTypeBlock() == RelatedContentBlock::SHOW_LINKED_BY) {
            return $this->getSortedLinkedByItems($query, $document, $block);
        } elseif ($block->getSortBy()) {
            $query->sort($block->getSortBy(), $block->getSortDirection());
        }

        return $query;
    }

    /**
     * @return Builder
     */
    protected function getLinkedByQuery(Content $document, RelatedContentBlock $block)
    {
        $ids = [];

        foreach ($document->getReferencesByRelationId($block->getRelation()->getId()) as $content) {
            $ids[$content->getId()] = $content->getId();
        }

        return $this->dm->createQueryBuilder(Content::class)
                        ->field('_id')->in($ids);
    }

    /**
     * @return ArrayCollection
     */
    protected function getSortedLinkedByItems(Builder $query, Content $document, RelatedContentBlock $block)
    {
        $items = new ArrayCollection();
        $allowedItems = [];

        $result = $query->getQuery()->execute();
        if (is_iterable($result)) {
            foreach ($result as $item) {
                $allowedItems[$item->getId()] = true;
            }
        }

        foreach ($document->getReferencesByRelationId($block->getRelation()->getId()) as $content) {
            if (!isset($allowedItems[$content->getId()])) {
                continue;
            }

            $items[] = $content;
        }

        return $items;
    }

    /**
     * Avoids the expensive paginator count query when the block is capped to a single page.
     */
    /**
     * @param Builder|iterable<mixed>|mixed $target
     *
     * @return array<int, mixed>|mixed
     */
    private function materializeSinglePageTarget(mixed $target, int $maxItems): mixed
    {
        if ($target instanceof Builder) {
            $query = clone $target;
            $query->limit($maxItems);

            $items = [];
            $result = $query->getQuery()->execute();
            if (is_iterable($result)) {
                foreach ($result as $item) {
                    $items[] = $item;
                }
            }

            return $items;
        }

        if (\is_array($target)) {
            return \array_slice($target, 0, $maxItems);
        }

        if (is_iterable($target)) {
            $items = [];
            foreach ($target as $item) {
                $items[] = $item;
                if (\count($items) >= $maxItems) {
                    break;
                }
            }

            return $items;
        }

        return $target;
    }
}
