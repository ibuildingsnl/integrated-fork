<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\Service;

use Psr\Cache\CacheItemPoolInterface;

final class SitemapCacheVersionManager
{
    private const CACHE_KEY_PREFIX = 'integrated_sitemap_version_';
    private const VERSION_TTL_SECONDS = 31536000;

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function getChannelVersion(string $channelId): int
    {
        return $this->getVersion($this->buildVersionScope('channel', $channelId));
    }

    public function bumpChannel(string $channelId): void
    {
        $this->bumpVersion($this->buildVersionScope('channel', $channelId));
    }

    public function getChannelContentVersion(string $channelId): int
    {
        return $this->getVersion($this->buildVersionScope('content', $channelId));
    }

    public function bumpChannelContent(string $channelId): void
    {
        $this->bumpVersion($this->buildVersionScope('content', $channelId));
    }

    public function getChannelTypeVersion(string $channelId, string $contentType): int
    {
        return $this->getVersion($this->buildVersionScope('type', $channelId.'|'.$contentType));
    }

    public function bumpChannelType(string $channelId, string $contentType): void
    {
        $this->bumpVersion($this->buildVersionScope('type', $channelId.'|'.$contentType));
    }

    public function getChannelPagesVersion(string $channelId): int
    {
        return $this->getVersion($this->buildVersionScope('pages', $channelId));
    }

    public function bumpChannelPages(string $channelId): void
    {
        $this->bumpVersion($this->buildVersionScope('pages', $channelId));
    }

    public function getChannelNewsVersion(string $channelId): int
    {
        return $this->getVersion($this->buildVersionScope('news', $channelId));
    }

    public function bumpChannelNews(string $channelId): void
    {
        $this->bumpVersion($this->buildVersionScope('news', $channelId));
    }

    private function getVersion(string $scope): int
    {
        $item = $this->cache->getItem($this->toCacheKey($scope));
        if (!$item->isHit()) {
            $item->set(1);
            $item->expiresAfter(self::VERSION_TTL_SECONDS);
            $this->cache->save($item);

            return 1;
        }

        $value = (int) $item->get();
        if ($value < 1) {
            return 1;
        }

        return $value;
    }

    private function bumpVersion(string $scope): void
    {
        $cacheKey = $this->toCacheKey($scope);
        $item = $this->cache->getItem($cacheKey);
        $nextVersion = $item->isHit() ? max(1, (int) $item->get()) + 1 : 2;
        $item->set($nextVersion);
        $item->expiresAfter(self::VERSION_TTL_SECONDS);
        $this->cache->save($item);
    }

    private function buildVersionScope(string $bucket, string $value): string
    {
        return $bucket.'|'.$value;
    }

    private function toCacheKey(string $scope): string
    {
        return self::CACHE_KEY_PREFIX.sha1($scope);
    }
}
