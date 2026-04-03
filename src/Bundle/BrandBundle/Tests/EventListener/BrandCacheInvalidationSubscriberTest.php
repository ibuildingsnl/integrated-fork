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
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('delete')
            ->with(CachedBrandRepository::ALL_CACHE_KEY)
            ->willReturn(true);

        $subscriber = new BrandCacheInvalidationSubscriber($cache);
        $subscriber->invalidate();
    }
}
