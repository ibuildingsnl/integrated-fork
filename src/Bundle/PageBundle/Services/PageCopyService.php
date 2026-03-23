<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\MappingException as MappingExceptionAlias;
use Doctrine\ODM\MongoDB\MongoDBException as MongoDBExceptionAlias;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Item;
use Integrated\Bundle\PageBundle\Document\Page\Grid\ItemsInterface;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageBlockCloner;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageCopyInstruction;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageCopyRequest;

class PageCopyService
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var RouteCache
     */
    private $routeCache;

    public function __construct(DocumentManager $documentManager, RouteCache $routeCache, private readonly PageBlockCloner $pageBlockCloner)
    {
        $this->documentManager = $documentManager;
        $this->routeCache = $routeCache;
    }

    /**
     * @throws MongoDBExceptionAlias
     * @throws MappingExceptionAlias
     */
    public function copyPages(PageCopyRequest $request)
    {
        $targetChannel = $this->documentManager->getRepository(Channel::class)->find($request->getTargetChannelId());
        if ($targetChannel === null) {
            throw new \Exception('Channel not found');
        }

        $result = $this->documentManager->getRepository(Page::class)->findBy(
            [
                'channel.$id' => $request->getSourceChannelId(),
            ]
        );

        $existingPages = [];
        $existingBlocks = [];
        $copiedPages = 0;

        /** @var Page $page */
        foreach ($result as $page) {
            $pageInstruction = $request->getPageInstruction((string) $page->getId());
            if ($pageInstruction instanceof PageCopyInstruction) {
                $existingPage = $this->findExistingTargetPage($targetChannel->getId(), (string) $page->getPath(), $existingPages);

                if ($existingPage !== null) {
                    if (!$pageInstruction->shouldOverwrite()) {
                        throw new \InvalidArgumentException(\sprintf(
                            'Target page "%s" already exists in channel "%s".',
                            (string) $page->getPath(),
                            (string) $targetChannel->getId()
                        ));
                    }

                    $this->documentManager->remove($existingPage);
                    $this->documentManager->flush();
                    $existingPages[$targetChannel->getId().'|'.(string) $page->getPath()] = null;
                }

                $this->documentManager->detach($page);

                /** @var Page $copiedPage */
                $copiedPage = clone $page;
                $copiedPage->setCreatedAt(new \DateTime());
                $copiedPage->setChannel($targetChannel);

                foreach ($copiedPage->getGrids() as $grid) {
                    $this->copyGridBlocks($grid, $pageInstruction, $copiedPage, $existingBlocks);
                }
                $copiedPage->updateBlockIdsFromGrids();

                $this->documentManager->persist($copiedPage);
                $this->documentManager->flush();
                ++$copiedPages;
            }
        }

        if ($copiedPages > 0) {
            $this->routeCache->clear();
        }
    }

    /**
     * @throws \Exception
     */
    /**
     * @param array<string, Block|null> $existingBlocks
     */
    private function copyGridBlocks(ItemsInterface $grid, PageCopyInstruction $pageInstruction, AbstractPage $copiedPage, array &$existingBlocks)
    {
        $gridItems = $grid->getItems();
        foreach ($gridItems as $item) {
            if (!$item instanceof Item) {
                continue;
            }

            $block = $item->getBlock();

            if ($block instanceof Block) {
                $blockInstruction = $pageInstruction->getBlockInstruction((string) $block->getId());
                if ($blockInstruction !== null && $blockInstruction->isClone()) {
                    $newBlockId = (string) $blockInstruction->getTargetBlockId();
                    $existingBlock = $this->findExistingBlock($newBlockId, $existingBlocks);

                    if ($existingBlock instanceof Block) {
                        $item->setBlock($existingBlock);
                    } else {
                        $copiedBlock = $this->pageBlockCloner->cloneBlock($block, $newBlockId, $copiedPage);
                        $this->documentManager->persist($copiedBlock);
                        $existingBlocks[$newBlockId] = $copiedBlock;
                        $item->setBlock($copiedBlock);
                    }
                }
            }

            if ($item->getRow()) {
                foreach ($item->getRow()->getColumns() as $column) {
                    $this->copyGridBlocks($column, $pageInstruction, $copiedPage, $existingBlocks);
                }
            }
        }
    }

    /**
     * @param array<string, Page|null> $existingPages
     */
    private function findExistingTargetPage(string $targetChannelId, string $path, array &$existingPages): ?Page
    {
        $key = $targetChannelId.'|'.$path;
        if (!\array_key_exists($key, $existingPages)) {
            $page = $this->documentManager->getRepository(Page::class)->findOneBy([
                'channel.$id' => $targetChannelId,
                'path' => $path,
            ]);
            $existingPages[$key] = $page instanceof Page ? $page : null;
        }

        return $existingPages[$key];
    }

    /**
     * @param array<string, Block|null> $existingBlocks
     */
    private function findExistingBlock(string $blockId, array &$existingBlocks): ?Block
    {
        if (!\array_key_exists($blockId, $existingBlocks)) {
            $block = $this->documentManager->getRepository(Block::class)->findOneBy(['_id' => $blockId]);
            $existingBlocks[$blockId] = $block instanceof Block ? $block : null;
        }

        return $existingBlocks[$blockId];
    }
}
