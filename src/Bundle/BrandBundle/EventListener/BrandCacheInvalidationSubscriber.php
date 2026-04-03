<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\BrandBundle\Event\BrandAddedEvent;
use Integrated\Bundle\BrandBundle\Event\BrandRemovedEvent;
use Integrated\Bundle\BrandBundle\Event\BrandUpdatedEvent;
use Integrated\Bundle\BrandBundle\Infrastructure\CachedBrandRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class BrandCacheInvalidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BrandAddedEvent::class => 'invalidate',
            BrandUpdatedEvent::class => 'invalidate',
            BrandRemovedEvent::class => 'invalidate',
        ];
    }

    public function invalidate(): void
    {
        $this->cache->delete(CachedBrandRepository::ALL_CACHE_KEY);
    }
}
