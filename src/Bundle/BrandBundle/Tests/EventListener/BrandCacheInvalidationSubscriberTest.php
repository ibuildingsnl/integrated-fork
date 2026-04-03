<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\EventListener;

use Integrated\Bundle\BrandBundle\EventListener\BrandCacheInvalidationSubscriber;
use Integrated\Bundle\BrandBundle\Infrastructure\CachedBrandRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

final class BrandCacheInvalidationSubscriberTest extends TestCase
{
    public function testInvalidateDeletesBrandCacheKey(): void
    {
        $deletedKeys = [];

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::exactly(2))
            ->method('delete')
            ->willReturnCallback(function (string $key) use (&$deletedKeys): bool {
                $deletedKeys[] = $key;

                return true;
            });

        $subscriber = new BrandCacheInvalidationSubscriber($cache);
        $subscriber->invalidate();

        self::assertSame(
            [
                CachedBrandRepository::ALL_CACHE_KEY,
                CachedBrandRepository::CHANNEL_LOOKUP_CACHE_KEY,
            ],
            $deletedKeys
        );
    }
}
