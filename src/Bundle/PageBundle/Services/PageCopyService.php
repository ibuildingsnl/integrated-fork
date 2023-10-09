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
use Doctrine\ODM\MongoDB\MongoDBException as MongoDBExceptionAlias;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Item;
use Integrated\Bundle\PageBundle\Document\Page\Grid\ItemsInterface;
use Integrated\Bundle\PageBundle\Document\Page\Page;

class PageCopyService
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly ChannelRepository $channelRepository,
        private readonly DocumentRepository $pageRepository,
        private readonly RouteCache $routeCache
    ) {
    }

    /**
     * @throws MongoDBExceptionAlias
     * @throws \Exception
     */
    public function copyPages(array $data): void
    {
        $targetChannel = $this->channelRepository->find($data['targetChannel']);
        if ($targetChannel === null) {
            throw new \Exception('Channel not found');
        }

        $result = $this->pageRepository->findBy(
            [
                'channel.$id' => $data['sourceChannel'],
            ]
        );

        /** @var Page $page */
        foreach ($result as $page) {
            if (isset($data['pages']['page'.$page->getId()]['selected']) && $data['pages']['page'.$page->getId()]['selected'] === true) {
                $existingPage = $this->pageRepository->findOneBy(
                    [
                        'channel.$id' => $targetChannel->getId(),
                        'path' => $page->getPath(),
                    ]
                );

                if ($existingPage !== null) {
                    $this->documentManager->remove($existingPage);
                }

                $this->documentManager->detach($page);

                /** @var Page $copiedPage */
                $copiedPage = clone $page;
                $copiedPage->setCreatedAt(new \DateTime());
                $copiedPage->setChannel($targetChannel);

                foreach ($copiedPage->getGrids() as $key => $grid) {
                    $this->copyGridBlocks($grid, $data['pages']['page'.$page->getId()]['blocks']);
                }

                $this->documentManager->persist($copiedPage);
                $this->documentManager->flush();

                $this->routeCache->clear();
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function copyGridBlocks(ItemsInterface $grid, array $data): void
    {
        $gridItems = $grid->getItems();
        foreach ($gridItems as $item) {
            if (!$item instanceof Item) {
                continue;
            }

            $block = $item->getBlock();

            if ($block instanceof Block) {
                // copy block
                if (isset($data['block_'.$block->getId()]['operation']) && $data['block_'.$block->getId()]['operation'] == 'clone') {
                    $copiedBlock = clone $block;
                    $copiedBlock->setId($data['block_'.$block->getId()]['newBlockId']);
                    $copiedBlock->setCreatedAt(new \DateTime());

                    $this->documentManager->persist($copiedBlock);

                    $item->setBlock($copiedBlock);
                }
            }

            if ($item->getRow()) {
                foreach ($item->getRow()->getColumns() as $column) {
                    $this->copyGridBlocks($column, $data);
                }
            }
        }
    }
}
