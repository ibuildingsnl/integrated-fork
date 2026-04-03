<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Resolver;

use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Channel\Connector\Config\ResolverInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class ThemeResolver
{
    private const THEME_CACHE_KEY_PREFIX = 'integrated_page_theme_';
    private const THEME_CACHE_TTL_SECONDS = 86400;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cache;

    public function __construct(ResolverInterface $resolver, ThemeManager $themeManager, ?CacheItemPoolInterface $cache = null)
    {
        $this->resolver = $resolver;
        $this->themeManager = $themeManager;
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getTheme(ChannelInterface $channel)
    {
        $channelId = trim((string) $channel->getId());

        if ('' !== $channelId) {
            $cachedTheme = $this->findThemeFromCache($channelId);
            if (null !== $cachedTheme) {
                return $cachedTheme;
            }
        }

        if ($configs = $this->resolver->getConfigs($channel)) {
            foreach ($configs as $config) {
                if ($config->getAdapter() === 'website') {
                    $theme = $config->getOptions()->get('theme');

                    if ($this->themeManager->hasTheme($theme)) {
                        if ('' !== $channelId) {
                            $this->saveThemeToCache($channelId, $theme);
                        }

                        return $theme;
                    }
                }
            }
        }

        if ('' !== $channelId) {
            $this->saveThemeToCache($channelId, 'default');
        }

        return 'default';
    }

    public function invalidateForChannel(ChannelInterface $channel): void
    {
        $channelId = trim((string) $channel->getId());
        if ('' === $channelId) {
            return;
        }

        $this->invalidateForChannelId($channelId);
    }

    public function invalidateForChannelId(string $channelId): void
    {
        $normalizedChannelId = trim($channelId);
        if ('' === $normalizedChannelId || null === $this->cache) {
            return;
        }

        $this->cache->deleteItem($this->getThemeCacheKey($normalizedChannelId));
    }

    public function clearCache(): void
    {
        if (null === $this->cache) {
            return;
        }

        $this->cache->clear();
    }

    private function findThemeFromCache(string $channelId): ?string
    {
        if (null === $this->cache) {
            return null;
        }

        $cacheItem = $this->cache->getItem($this->getThemeCacheKey($channelId));
        if (!$cacheItem->isHit()) {
            return null;
        }

        $theme = trim((string) $cacheItem->get());
        if ('' === $theme) {
            $this->cache->deleteItem($cacheItem->getKey());

            return null;
        }

        if (!$this->themeManager->hasTheme($theme) && $theme !== 'default') {
            $this->cache->deleteItem($cacheItem->getKey());

            return null;
        }

        return $theme;
    }

    private function saveThemeToCache(string $channelId, string $theme): void
    {
        if (null === $this->cache) {
            return;
        }

        $cacheItem = $this->cache->getItem($this->getThemeCacheKey($channelId));
        $cacheItem->set($theme);
        $cacheItem->expiresAfter(self::THEME_CACHE_TTL_SECONDS);
        $this->cache->save($cacheItem);
    }

    private function getThemeCacheKey(string $channelId): string
    {
        return self::THEME_CACHE_KEY_PREFIX.md5($channelId);
    }
}
