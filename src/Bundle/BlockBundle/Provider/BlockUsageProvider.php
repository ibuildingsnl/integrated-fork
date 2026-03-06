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
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class BlockUsageProvider
{
    public const CACHE_KEY = 'integrated_block.provider.block_usage.v1';

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
     * @var ChannelInterface[]
     */
    protected $channels = [];

    public function __construct(
        DocumentManager $manager,
        private ?CacheInterface $cache = null
    ) {
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
        $data = null;
        if ($this->cache instanceof CacheInterface) {
            $data = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
                $item->expiresAfter(600);

                return $this->buildUsageMaps();
            });
        } else {
            $data = $this->buildUsageMaps();
        }

        $this->blockPages = \is_array($data['blockPages'] ?? null) ? $data['blockPages'] : [];
        $this->channelBlocks = \is_array($data['channelBlocks'] ?? null) ? $data['channelBlocks'] : [];
    }

    private function buildUsageMaps(): array
    {
        $blockPages = [];
        $channelBlocks = [];

        $pages = $this->manager->createQueryBuilder(Page::class)
            ->hydrate(false)
            ->select(['title', 'channel', 'locked', 'blockIds', 'grids'])
            ->getQuery()
            ->getIterator();

        foreach ($pages as $page) {
            if (!\is_array($page)) {
                continue;
            }

            $pageData = array_intersect_key($page, array_flip(['_id', 'title', 'locked', 'channel']));
            if (!\array_key_exists('_id', $pageData) || !\is_scalar($pageData['_id'])) {
                continue;
            }

            $pageId = (string) $pageData['_id'];
            $channelId = $this->extractChannelId($page);

            foreach ($this->extractBlockIds($page) as $blockId) {
                $blockPages[$blockId][$pageId] = $pageData;

                if ($channelId !== null) {
                    $channelBlocks[$channelId][$blockId] = $blockId;
                }
            }
        }

        return [
            'blockPages' => $blockPages,
            'channelBlocks' => $channelBlocks,
        ];
    }

    /**
     * @return string|null
     */
    private function extractChannelId(array $page)
    {
        if (
            \array_key_exists('channel', $page)
            && \is_array($page['channel'])
            && \array_key_exists('$id', $page['channel'])
            && \is_scalar($page['channel']['$id'])
        ) {
            return (string) $page['channel']['$id'];
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function extractBlockIds(array $page): array
    {
        $indexedBlockIds = [];

        if (\array_key_exists('blockIds', $page) && \is_array($page['blockIds'])) {
            foreach ($page['blockIds'] as $blockId) {
                if (!\is_scalar($blockId)) {
                    continue;
                }

                $value = trim((string) $blockId);
                if ($value === '') {
                    continue;
                }

                $indexedBlockIds[$value] = true;
            }
        }

        if (\count($indexedBlockIds) > 0) {
            return array_keys($indexedBlockIds);
        }

        if (!\array_key_exists('grids', $page) || !\is_array($page['grids'])) {
            return [];
        }

        foreach ($page['grids'] as $grid) {
            if (!\is_array($grid) || !\array_key_exists('items', $grid) || !\is_array($grid['items'])) {
                continue;
            }

            $this->extractBlockIdsFromItems($grid['items'], $indexedBlockIds);
        }

        return array_keys($indexedBlockIds);
    }

    /**
     * Recursive iteration items to register block ids.
     *
     * @param array $items
     * @param array<string, bool> $indexedBlockIds
     */
    private function extractBlockIdsFromItems(array $items, array &$indexedBlockIds): void
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
                        $this->extractBlockIdsFromItems($column['items'], $indexedBlockIds);
                    }
                }
            }

            if (
                \array_key_exists('block', $item)
                && \is_array($item['block'])
                && \array_key_exists('$id', $item['block'])
                && \is_scalar($item['block']['$id'])
            ) {
                $blockId = (string) $item['block']['$id'];
                if ($blockId !== '') {
                    $indexedBlockIds[$blockId] = true;
                }
            }
        }
    }
}
