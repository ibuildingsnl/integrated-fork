<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Common\Content\Channel\ChannelInterface;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class BlockUsageProvider
{
    /**
     * @var DocumentManager
     */
    protected $manager;

    /**
     * @var array|null
     */
    protected $blockPages;

    /**
     * @var array|null
     */
    protected $channelBlocks;

    /**
     * @var array|null
     */
    protected $currentPage;

    /**
     * @var array|null
     */
    protected $currentChannel;

    /**
     * @var ChannelInterface[]
     */
    protected $channels = [];

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string|null $blockId
     *
     * @return array|null
     */
    public function getPagesPerBlock($blockId = null)
    {
        if (null === $this->blockPages) {
            // loads blockPages
            $this->convertPages();

            // to prevent doing same logic every time if there are no results
            if (null === $this->blockPages) {
                $this->blockPages = [];
            }
        }

        if (null !== $blockId) {
            return \array_key_exists($blockId, $this->blockPages) ? $this->blockPages[$blockId] : null;
        }

        return $this->blockPages;
    }

    /**
     * @param string|null $channelId
     *
     * @return array
     */
    public function getBlocksPerChannel($channelId = null)
    {
        if (null === $this->channelBlocks) {
            // loads channelBlocks
            $this->convertPages();

            // to prevent doing same logic every time if there are no results
            if (null === $this->channelBlocks) {
                $this->channelBlocks = [];
            }
        }

        if (null !== $channelId) {
            return \array_key_exists($channelId, $this->channelBlocks) ? $this->channelBlocks[$channelId] : [];
        }

        return $this->channelBlocks;
    }

    /**
     * @param string $id
     *
     * @return ChannelInterface|null
     */
    public function getChannel($id)
    {
        if (!\array_key_exists($id, $this->channels)) {
            $this->channels[$id] = $this->manager->getRepository(Channel::class)->find($id);
        }

        return $this->channels[$id];
    }

    /**
     * iterate pages to filter blocks.
     */
    protected function convertPages()
    {
        $pages = $this->manager->createQueryBuilder(Page::class)
            ->hydrate(false)
            ->select(['title', 'channel', 'locked', 'grids'])
            ->getQuery()
            ->getIterator();

        foreach ($pages as $page) {
            if (!\is_array($page) || !\array_key_exists('grids', $page) || !\is_array($page['grids'])) {
                continue;
            }

            $this->currentPage = array_intersect_key($page, array_flip(['_id', 'title', 'locked', 'channel']));

            $this->currentChannel = null;
            if (
                \array_key_exists('channel', $page)
                && \is_array($page['channel'])
                && \array_key_exists('$id', $page['channel'])
                && \is_scalar($page['channel']['$id'])
            ) {
                $this->currentChannel = (string) $page['channel']['$id'];
            }

            foreach ($page['grids'] as $grid) {
                if (!\is_array($grid) || !\array_key_exists('items', $grid) || !\is_array($grid['items'])) {
                    continue;
                }
                $this->filterItems($grid['items']);
            }
        }
    }

    /**
     * Recursive iteration items to register blocks per page and per channel.
     *
     * @param array $items
     */
    protected function filterItems($items)
    {
        foreach ($items as $item) {
            if (!\is_array($item)) {
                continue;
            }

            if (
                \array_key_exists('row', $item)
                && \is_array($item['row'])
                && \array_key_exists('columns', $item['row'])
                && \is_array($item['row']['columns'])
            ) {
                foreach ($item['row']['columns'] as $column) {
                    if (\is_array($column) && \array_key_exists('items', $column) && \is_array($column['items'])) {
                        $this->filterItems($column['items']);
                    }
                }
            } elseif (
                \array_key_exists('block', $item)
                && \is_array($item['block'])
                && \array_key_exists('$id', $item['block'])
                && \is_scalar($item['block']['$id'])
                && \array_key_exists('_id', $this->currentPage)
                && \is_scalar($this->currentPage['_id'])
            ) {
                $blockId = (string) $item['block']['$id'];
                $pageId = (string) $this->currentPage['_id'];
                $this->blockPages[$blockId][$pageId] = $this->currentPage;

                if ($this->currentChannel) {
                    $this->channelBlocks[$this->currentChannel][$blockId] = $blockId;
                }
            }
        }
    }
}
