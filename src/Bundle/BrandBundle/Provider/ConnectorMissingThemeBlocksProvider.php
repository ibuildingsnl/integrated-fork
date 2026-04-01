<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Content\Channel\ChannelInterface;

class ConnectorMissingThemeBlocksProvider
{
    public function __construct(
        private readonly BlockUsageProvider $blockUsageProvider,
        private readonly ThemeResolver $themeResolver,
        private readonly ThemeManager $themeManager,
        private readonly DocumentManager $documentManager,
    ) {
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     usages: array<int, array<string, string>>
     * }>
     */
    public function getMissingBlocksForChannel(?ChannelInterface $channel): array
    {
        if (!$channel instanceof ChannelInterface || trim((string) $channel->getId()) === '') {
            return [];
        }

        $themeChain = $this->resolveThemeChain($this->themeResolver->getTheme($channel));
        if ($themeChain === []) {
            return [];
        }

        $relevantUsages = [];
        $channelId = (string) $channel->getId();

        foreach ($this->blockUsageProvider->getTemplateUsagesPerBlock() as $blockId => $usages) {
            if (!is_string($blockId) || $blockId === '' || !is_array($usages)) {
                continue;
            }

            foreach ($usages as $usage) {
                if (!is_array($usage)) {
                    continue;
                }

                $themeId = trim((string) ($usage['theme'] ?? ''));
                if ($themeId === '' || !in_array($themeId, $themeChain, true)) {
                    continue;
                }

                $usageChannelId = trim((string) ($usage['channel_id'] ?? ''));
                if ($usageChannelId !== '' && $usageChannelId !== $channelId) {
                    continue;
                }

                $relevantUsages[$blockId][] = $usage;
            }
        }

        if ($relevantUsages === []) {
            return [];
        }

        $existingBlockIds = $this->getExistingBlockIds(array_keys($relevantUsages));
        $missingBlocks = [];

        foreach ($relevantUsages as $blockId => $usages) {
            if (isset($existingBlockIds[$blockId])) {
                continue;
            }

            usort($usages, static function (array $left, array $right): int {
                return [$left['theme'] ?? '', $left['template'] ?? ''] <=> [$right['theme'] ?? '', $right['template'] ?? ''];
            });

            $missingBlocks[] = [
                'id' => $blockId,
                'usages' => $usages,
            ];
        }

        usort($missingBlocks, static fn (array $left, array $right): int => $left['id'] <=> $right['id']);

        return $missingBlocks;
    }

    /**
     * @param string[] $blockIds
     *
     * @return array<string, true>
     */
    private function getExistingBlockIds(array $blockIds): array
    {
        if ($blockIds === []) {
            return [];
        }

        $repository = $this->documentManager->getRepository(Block::class);
        $blocks = $repository->findBy(['id' => ['$in' => array_values($blockIds)]]);
        $existing = [];

        foreach ($blocks as $block) {
            if (!$block instanceof Block) {
                continue;
            }

            $blockId = trim((string) $block->getId());
            if ($blockId === '') {
                continue;
            }

            $existing[$blockId] = true;
        }

        return $existing;
    }

    /**
     * @return string[]
     */
    private function resolveThemeChain(string $themeId): array
    {
        if (!$this->themeManager->hasTheme($themeId)) {
            return [];
        }

        $resolved = [];
        $queue = [$themeId];

        while ($queue !== []) {
            $currentThemeId = array_shift($queue);
            if (!is_string($currentThemeId) || $currentThemeId === '' || isset($resolved[$currentThemeId])) {
                continue;
            }

            $resolved[$currentThemeId] = $currentThemeId;
            $theme = $this->themeManager->getTheme($currentThemeId);

            foreach ($theme->getFallback() as $fallbackThemeId) {
                if (is_string($fallbackThemeId) && $fallbackThemeId !== '') {
                    $queue[] = $fallbackThemeId;
                }
            }
        }

        return array_values($resolved);
    }
}
