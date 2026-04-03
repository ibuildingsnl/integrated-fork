<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Infrastructure;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Infrastructure\CachedBrandRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

final class CachedBrandRepositoryTest extends TestCase
{
    public function testAllDelegatesToInnerRepository(): void
    {
        $inner = $this->createMock(BrandRepository::class);
        $cache = $this->createMock(CacheInterface::class);
        $brand = new Brand();

        $inner
            ->expects(self::exactly(2))
            ->method('all')
            ->willReturn([$brand]);

        $repository = new CachedBrandRepository($inner, $cache);

        self::assertEquals([$brand], $repository->all());
        self::assertEquals([$brand], $repository->all());
    }

    public function testAddClearsAllCacheKey(): void
    {
        $inner = $this->createMock(BrandRepository::class);
        $cache = $this->createMock(CacheInterface::class);
        $newBrand = new Brand();

        $inner
            ->expects(self::once())
            ->method('add')
            ->with($newBrand);

        $cache
            ->expects(self::once())
            ->method('delete')
            ->with(CachedBrandRepository::ALL_CACHE_KEY)
            ->willReturn(true);

        $repository = new CachedBrandRepository($inner, $cache);
        $repository->add($newBrand);
    }
}
