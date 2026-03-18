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
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;

class TemplateBlockUsageProvider implements BlockUsageSourceInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly ThemeManager $themeManager,
        private readonly ThemeResolver $themeResolver,
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{
     *     blockTemplates: array<string, array<string, array<string, string>>>,
     *     channelBlocks: array<string, array<string, string>>
     * }
     */
    public function getUsageMaps(): array
    {
        $blockTemplates = [];
        $channelBlocks = [];
        $channelsByTheme = [];

        foreach ($this->getChannels() as $channel) {
            foreach ($this->resolveThemeChain($this->themeResolver->getTheme($channel)) as $themeId) {
                $channelsByTheme[$themeId][$channel->getId()] = $channel;
            }
        }

        foreach ($channelsByTheme as $themeId => $channels) {
            foreach ($this->getThemeTemplateFiles($themeId) as $templateFile) {
                $content = file_get_contents($templateFile);
                if (!\is_string($content) || trim($content) === '') {
                    continue;
                }

                $templatePath = $this->normalizeTemplatePath($templateFile);

                foreach ($this->extractLiteralBlockIds($content) as $blockId) {
                    $this->registerUsage($blockTemplates, $blockId, $templatePath, $themeId);
                }

                foreach ($this->extractFormattedChannelPatterns($content) as $pattern) {
                    foreach ($channels as $channel) {
                        $blockId = str_contains($pattern, '%s') ? \sprintf($pattern, $channel->getId()) : $pattern;
                        $this->registerUsage($blockTemplates, $blockId, $templatePath, $themeId, $channel->getId());
                        $channelBlocks[$channel->getId()][$blockId] = $blockId;
                    }
                }

                foreach ($this->extractChannelIdPrefixes($content) as $prefix) {
                    foreach ($channels as $channel) {
                        $blockId = $prefix.$channel->getId();
                        $this->registerUsage($blockTemplates, $blockId, $templatePath, $themeId, $channel->getId());
                        $channelBlocks[$channel->getId()][$blockId] = $blockId;
                    }
                }

                foreach ($this->extractChannelLanguagePrefixes($content) as $languagePattern) {
                    foreach ($channels as $channel) {
                        $blockId = $languagePattern['prefix'].($channel->getLanguage() ?: $languagePattern['default']);
                        $this->registerUsage($blockTemplates, $blockId, $templatePath, $themeId, $channel->getId());
                        $channelBlocks[$channel->getId()][$blockId] = $blockId;
                    }
                }

                foreach ($this->extractChannelBlockPrefixes($content) as $prefix) {
                    foreach ($channels as $channel) {
                        $blockId = $prefix.'_'.$channel->getId();
                        $this->registerUsage($blockTemplates, $blockId, $templatePath, $themeId, $channel->getId());
                        $channelBlocks[$channel->getId()][$blockId] = $blockId;
                    }
                }
            }
        }

        return [
            'blockTemplates' => $blockTemplates,
            'channelBlocks' => $channelBlocks,
        ];
    }

    /**
     * @return Channel[]
     */
    private function getChannels(): array
    {
        return array_values(array_filter(
            $this->manager->getRepository(Channel::class)->findAll(),
            static fn (Channel $channel): bool => trim((string) $channel->getId()) !== ''
        ));
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
            if (!\is_string($currentThemeId) || $currentThemeId === '' || isset($resolved[$currentThemeId])) {
                continue;
            }

            $resolved[$currentThemeId] = $currentThemeId;
            $theme = $this->themeManager->getTheme($currentThemeId);

            foreach ($theme->getFallback() as $fallbackThemeId) {
                if (\is_string($fallbackThemeId) && $fallbackThemeId !== '') {
                    $queue[] = $fallbackThemeId;
                }
            }
        }

        return array_values($resolved);
    }

    /**
     * @return string[]
     */
    private function getThemeTemplateFiles(string $themeId): array
    {
        if (!$this->themeManager->hasTheme($themeId)) {
            return [];
        }

        $files = [];
        foreach ($this->themeManager->getTheme($themeId)->getPaths() as $path) {
            foreach ($this->resolveThemeDirectories($path) as $directory) {
                if (!is_dir($directory)) {
                    continue;
                }

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'twig') {
                        continue;
                    }

                    $filePath = $file->getPathname();
                    $files[$filePath] = $filePath;
                }
            }
        }

        ksort($files);

        return array_values($files);
    }

    /**
     * @return string[]
     */
    private function resolveThemeDirectories(string $path): array
    {
        $directories = [];

        if (str_starts_with($path, '@')) {
            foreach ($this->themeManager->locateResources($path) as $resolvedPath) {
                $realPath = realpath($resolvedPath);
                if ($realPath !== false) {
                    $directories[$realPath] = $realPath;
                }
            }

            return array_values($directories);
        }

        $realPath = realpath($path);
        if ($realPath !== false) {
            return [$realPath];
        }

        $realPath = realpath(rtrim($this->projectDir, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR.ltrim($path, \DIRECTORY_SEPARATOR));

        return $realPath !== false ? [$realPath] : [];
    }

    /**
     * @return string[]
     */
    private function extractLiteralBlockIds(string $content): array
    {
        preg_match_all('/integrated_block\(\s*([\'"])([^\'"]+)\1\s*(?:,|\))/m', $content, $matches);

        return $this->normalizeScalarValues($matches[2]);
    }

    /**
     * @return string[]
     */
    private function extractFormattedChannelPatterns(string $content): array
    {
        preg_match_all(
            '/integrated_block\(\s*([\'"])([^\'"]+)\1\s*\|\s*format\(\s*app\.request(?:\.attributes)?\.get\(\s*([\'"])_channel\3\s*\)\s*\)\s*\)/m',
            $content,
            $matches
        );

        return $this->normalizeScalarValues($matches[2]);
    }

    /**
     * @return string[]
     */
    private function extractChannelIdPrefixes(string $content): array
    {
        preg_match_all('/integrated_block\(\s*([\'"])([^\'"]*)\1\s*~\s*_channel\.id\s*\)/m', $content, $matches);

        return $this->normalizeScalarValues($matches[2]);
    }

    /**
     * @return array<int, array{prefix: string, default: string}>
     */
    private function extractChannelLanguagePrefixes(string $content): array
    {
        preg_match_all(
            '/integrated_block\(\s*([\'"])([^\'"]*)\1\s*~\s*_channel\.language\|default\(\s*([\'"])([^\'"]+)\3\s*\)\s*\)/m',
            $content,
            $matches,
            \PREG_SET_ORDER
        );

        $patterns = [];

        foreach ($matches as $match) {
            $prefix = trim($match[2]);
            $default = trim($match[4]);

            if ($prefix === '') {
                continue;
            }

            $patterns[] = [
                'prefix' => $prefix,
                'default' => $default,
            ];
        }

        return $patterns;
    }

    /**
     * @return string[]
     */
    private function extractChannelBlockPrefixes(string $content): array
    {
        preg_match_all('/integrated_channel_block\(\s*([\'"])([^\'"]+)\1\s*,/m', $content, $matches);

        return $this->normalizeScalarValues($matches[2]);
    }

    /**
     * @param array<string, array<string, array<string, string>>> $blockTemplates
     */
    private function registerUsage(array &$blockTemplates, string $blockId, string $templatePath, string $themeId, ?string $channelId = null): void
    {
        $blockId = trim($blockId);
        if ($blockId === '') {
            return;
        }

        $key = $templatePath.'|'.$themeId.'|'.($channelId ?? '');
        $usage = [
            'template' => $templatePath,
            'theme' => $themeId,
        ];

        if ($channelId !== null) {
            $usage['channel_id'] = $channelId;
        }

        $blockTemplates[$blockId][$key] = $usage;
    }

    /**
     * @param array<int, mixed> $values
     *
     * @return string[]
     */
    private function normalizeScalarValues(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            if (!\is_scalar($value)) {
                continue;
            }

            $stringValue = trim((string) $value);
            if ($stringValue === '') {
                continue;
            }

            $normalized[$stringValue] = $stringValue;
        }

        return array_values($normalized);
    }

    private function normalizeTemplatePath(string $filePath): string
    {
        $realFilePath = realpath($filePath) ?: $filePath;
        $projectDir = rtrim($this->projectDir, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR;

        if (str_starts_with($realFilePath, $projectDir)) {
            return str_replace(\DIRECTORY_SEPARATOR, '/', substr($realFilePath, \strlen($projectDir)));
        }

        return str_replace(\DIRECTORY_SEPARATOR, '/', $realFilePath);
    }
}
