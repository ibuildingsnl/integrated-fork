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
use Integrated\Bundle\BlockBundle\Document\Block\ContainerBlock;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class BlockUsageProvider
{
    public const CACHE_KEY = 'integrated_block.provider.block_usage.v3';

    /**
     * @var DocumentManager
     */
    protected $manager;

    /**
     * @var array<string, array<string, array<string, mixed>>>|null
     */
    protected $blockPages;

    /**
     * @var array<string, array<string, string>>|null
     */
    protected $channelBlocks;

    /**
     * @var array<string, array<string, array<string, mixed>>>|null
     */
    protected $blockContainers;

    /**
     * @var array<string, array<string, array<string, string>>>|null
     */
    protected $blockTemplates;

    /**
     * @param iterable<BlockUsageSourceInterface> $usageSources
     */
    public function __construct(
        DocumentManager $manager,
        private ?CacheInterface $cache = null,
        iterable $usageSources = [],
    ) {
        $this->manager = $manager;
        $this->usageSources = $usageSources;
    }

    /**
     * @var ChannelInterface[]
     */
    protected $channels = [];

    /**
     * @var iterable<BlockUsageSourceInterface>
     */
    private iterable $usageSources;

    /**
     * @param string|null $blockId
     *
     * @return array<string, array<string, mixed>>|array<string, array<string, array<string, mixed>>>|null
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
     * @return array<string, string>|array<string, array<string, string>>
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
     * @param string|null $blockId
     *
     * @return array<string, array<string, mixed>>|array<string, array<string, array<string, mixed>>>|null
     */
    public function getContainerBlocksPerBlock($blockId = null)
    {
        if (null === $this->blockContainers) {
            $this->convertPages();

            if (null === $this->blockContainers) {
                $this->blockContainers = [];
            }
        }

        if (null !== $blockId) {
            return \array_key_exists($blockId, $this->blockContainers) ? $this->blockContainers[$blockId] : null;
        }

        return $this->blockContainers;
    }

    /**
     * @param string|null $blockId
     *
     * @return array<string, array<string, string>>|array<string, array<string, array<string, string>>>|null
     */
    public function getTemplateUsagesPerBlock($blockId = null)
    {
        if (null === $this->blockTemplates) {
            $this->convertPages();

            if (null === $this->blockTemplates) {
                $this->blockTemplates = [];
            }
        }

        if (null !== $blockId) {
            return \array_key_exists($blockId, $this->blockTemplates) ? $this->blockTemplates[$blockId] : null;
        }

        return $this->blockTemplates;
    }

    /**
     * @return string[]
     */
    public function getUsedBlockIds(): array
    {
        if (null === $this->blockPages || null === $this->blockContainers) {
            $this->convertPages();
        }

        $usedBlockIds = array_merge(
            array_keys(\is_array($this->blockPages) ? $this->blockPages : []),
            array_keys(\is_array($this->blockContainers) ? $this->blockContainers : []),
            array_keys(\is_array($this->blockTemplates) ? $this->blockTemplates : [])
        );

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): string => trim((string) $value),
            $usedBlockIds
        ))));
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

        $this->blockPages = $data['blockPages'];
        $this->channelBlocks = $data['channelBlocks'];
        $this->blockContainers = $data['blockContainers'];
        $this->blockTemplates = $data['blockTemplates'];
    }

    /**
     * @return array{
     *     blockPages: array<string, array<string, array<string, mixed>>>,
     *     channelBlocks: array<string, array<string, string>>,
     *     blockContainers: array<string, array<string, array<string, mixed>>>,
     *     blockTemplates: array<string, array<string, array<string, string>>>
     * }
     */
    private function buildUsageMaps(): array
    {
        $blockPages = [];
        $channelBlocks = [];
        $blockContainers = [];
        $blockTemplates = [];

        $pages = $this->manager->createQueryBuilder(AbstractPage::class)
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

        $containers = $this->manager->createQueryBuilder(ContainerBlock::class)
            ->hydrate(false)
            ->select(['title', 'items'])
            ->getQuery()
            ->getIterator();

        foreach ($containers as $container) {
            if (!\is_array($container) || !\array_key_exists('_id', $container) || !\is_scalar($container['_id'])) {
                continue;
            }

            $containerId = trim((string) $container['_id']);
            if ($containerId === '') {
                continue;
            }

            $containerData = [
                '_id' => $containerId,
                'title' => \is_scalar($container['title'] ?? null) && trim((string) $container['title']) !== ''
                    ? trim((string) $container['title'])
                    : $containerId,
            ];

            foreach ($this->extractContainerBlockIds($container) as $nestedBlockId) {
                $blockContainers[$nestedBlockId][$containerId] = $containerData;

                foreach (($blockPages[$containerId] ?? []) as $pageId => $pageData) {
                    $pageData['_used_via_container_id'] = $containerId;
                    $pageData['_used_via_container_title'] = $containerData['title'];
                    $blockPages[$nestedBlockId][$pageId] = $pageData;
                }
            }
        }

        foreach ($this->usageSources as $usageSource) {
            $usageMaps = $usageSource->getUsageMaps();

            if (\array_key_exists('blockTemplates', $usageMaps)) {
                foreach ($usageMaps['blockTemplates'] as $blockId => $usages) {
                    foreach ($usages as $usageKey => $usage) {
                        $blockTemplates[$blockId][$usageKey] = $usage;
                    }
                }
            }

            if (\array_key_exists('channelBlocks', $usageMaps)) {
                foreach ($usageMaps['channelBlocks'] as $channelId => $usedBlockIds) {
                    foreach ($usedBlockIds as $blockId) {
                        $value = trim((string) $blockId);
                        if ($value === '') {
                            continue;
                        }

                        $channelBlocks[$channelId][$value] = $value;
                    }
                }
            }
        }

        return [
            'blockPages' => $blockPages,
            'channelBlocks' => $channelBlocks,
            'blockContainers' => $blockContainers,
            'blockTemplates' => $blockTemplates,
        ];
    }

    /** @param array<string, mixed> $page */
    private function extractChannelId(array $page): ?string
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
    /**
     * @param array<string, mixed> $page
     *
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
     * @param array<int, mixed>   $items
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

    /**
     * @param array<string, mixed> $container
     *
     * @return string[]
     */
    private function extractContainerBlockIds(array $container): array
    {
        $items = $container['items'] ?? null;
        if (!\is_array($items)) {
            return [];
        }

        $indexedBlockIds = [];

        foreach ($items as $item) {
            if (
                !\is_array($item)
                || !\array_key_exists('block', $item)
                || !\is_array($item['block'])
                || !\array_key_exists('$id', $item['block'])
                || !\is_scalar($item['block']['$id'])
            ) {
                continue;
            }

            $blockId = trim((string) $item['block']['$id']);
            if ($blockId === '') {
                continue;
            }

            $indexedBlockIds[$blockId] = true;
        }

        return array_keys($indexedBlockIds);
    }
}
