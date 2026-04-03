<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Infrastructure;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class CachedBrandRepository implements BrandRepository
{
    public const ALL_CACHE_KEY = 'integrated_brand.repository.all.v1';

    public function __construct(
        private readonly BrandRepository $inner,
        private readonly CacheInterface $cache,
    ) {
    }

    /** @return Brand[] */
    public function all(): array
    {
        return $this->cache->get(self::ALL_CACHE_KEY, function (CacheItemInterface $item): array {
            $item->expiresAfter(3600);

            return $this->inner->all();
        });
    }

    public function find(string $id): ?Brand
    {
        return $this->inner->find($id);
    }

    public function add(Brand $brand): void
    {
        $this->inner->add($brand);
        $this->clearAllCache();
    }

    public function remove(Brand $brand): void
    {
        $this->inner->remove($brand);
        $this->clearAllCache();
    }

    public function clearAllCache(): void
    {
        $this->cache->delete(self::ALL_CACHE_KEY);
    }
}
