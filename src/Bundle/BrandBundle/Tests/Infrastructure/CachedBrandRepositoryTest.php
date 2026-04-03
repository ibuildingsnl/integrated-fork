<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Infrastructure;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Infrastructure\CachedBrandRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedBrandRepositoryTest extends TestCase
{
    public function testAllCachesAcrossRequestsViaCachePool(): void
    {
        $inner = $this->createMock(BrandRepository::class);
        $brand = new Brand();

        $inner
            ->expects(self::once())
            ->method('all')
            ->willReturn([$brand]);

        $repository = new CachedBrandRepository($inner, new ArrayAdapter());

        self::assertEquals([$brand], $repository->all());
        self::assertEquals([$brand], $repository->all());
    }

    public function testAddClearsCachedAllResult(): void
    {
        $inner = $this->createMock(BrandRepository::class);
        $firstBrand = new Brand();
        $updatedBrand = new Brand();
        $newBrand = new Brand();

        $inner
            ->expects(self::exactly(2))
            ->method('all')
            ->willReturnOnConsecutiveCalls([$firstBrand], [$updatedBrand]);

        $inner
            ->expects(self::once())
            ->method('add')
            ->with($newBrand);

        $repository = new CachedBrandRepository($inner, new ArrayAdapter());

        self::assertEquals([$firstBrand], $repository->all());

        $repository->add($newBrand);

        self::assertEquals([$updatedBrand], $repository->all());
    }
}
