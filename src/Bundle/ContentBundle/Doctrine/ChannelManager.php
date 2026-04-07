<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ChannelManager implements ChannelManagerInterface
{
    private const DOMAIN_LOOKUP_CACHE_KEY_PREFIX = 'integrated_content_channel_domain_';
    private const DOMAIN_LOOKUP_CACHE_VERSION_KEY = 'integrated_content_channel_domain_version';
    private const DOMAIN_LOOKUP_CACHE_VERSION_DEFAULT = 'v1';
    private const DOMAIN_LOOKUP_CACHE_MISS = '__null__';
    private const DOMAIN_LOOKUP_CACHE_TTL_SECONDS = 86400;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @var array<string, ChannelInterface|null>
     */
    private array $domainLookupCache = [];

    private ?CacheItemPoolInterface $cache;
    private ?string $domainLookupCacheVersion = null;

    public function __construct(ObjectManager $om, $class, ?CacheItemPoolInterface $cache = null)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository($class);
        $this->cache = $cache;

        if (!is_subclass_of($this->repository->getClassName(), 'Integrated\\Common\\Content\\Channel\\ChannelInterface')) {
            throw new \InvalidArgumentException(\sprintf('The class "%s" is not subclass of Integrated\\Common\\Content\\Channel\\ChannelInterface', $this->repository->getClassName()));
        }
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    public function create()
    {
        $class = $this->getClassName();

        return new $class();
    }

    public function persist(ChannelInterface $channel, $flush = true)
    {
        $this->om->persist($channel);

        if ($flush) {
            $this->om->flush();
        }
    }

    public function remove(ChannelInterface $channel, $flush = true)
    {
        $this->om->remove($channel);

        if ($flush) {
            $this->om->flush();
        }
    }

    public function clear()
    {
        $this->om->clear();
    }

    public function find($id)
    {
        return $this->normalizeChannel($this->repository->find($id));
    }

    public function findAll()
    {
        return $this->repository->findBy([], ['name' => 1]);
    }

    public function findByDomain($criteria)
    {
        $domain = $this->normalizeDomain((string) $criteria);

        if ('' === $domain) {
            return null;
        }

        if (\array_key_exists($domain, $this->domainLookupCache)) {
            return $this->domainLookupCache[$domain];
        }

        $cached = $this->findByDomainFromPersistentCache($domain);

        if ($cached instanceof ChannelInterface || null === $cached) {
            $this->domainLookupCache[$domain] = $cached;

            return $cached;
        }

        $channel = $this->normalizeChannel($this->repository->findOneBy(['domains' => $domain]));
        $fallbackDomain = null;

        if (!$channel) {
            $fallbackDomain = $this->getFallbackDomain($domain);

            if (null !== $fallbackDomain) {
                $channel = $this->normalizeChannel($this->repository->findOneBy(['domains' => $fallbackDomain]));
                $this->domainLookupCache[$fallbackDomain] = $channel;
                $this->saveDomainToPersistentCache($fallbackDomain, $channel);
            }
        }

        $this->domainLookupCache[$domain] = $channel;
        $this->saveDomainToPersistentCache($domain, $channel);

        if (null !== $fallbackDomain && !\array_key_exists($fallbackDomain, $this->domainLookupCache)) {
            $this->domainLookupCache[$fallbackDomain] = $channel;
            $this->saveDomainToPersistentCache($fallbackDomain, $channel);
        }

        return $channel;
    }

    public function invalidateDomainLookupCache(): void
    {
        $this->domainLookupCache = [];
        $this->domainLookupCacheVersion = $this->bumpDomainLookupCacheVersion();
    }

    public function findByName($criteria)
    {
        return $this->normalizeChannel($this->repository->findOneBy(['shortName' => $criteria]));
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function getClassName()
    {
        return $this->repository->getClassName();
    }

    private function normalizeDomain(string $domain): string
    {
        return strtolower(trim($domain));
    }

    private function findByDomainFromPersistentCache(string $domain): ChannelInterface|bool|null
    {
        if (null === $this->cache) {
            return false;
        }

        $cacheItem = $this->cache->getItem($this->getDomainLookupCacheKey($domain));

        if (!$cacheItem->isHit()) {
            return false;
        }

        $cachedValue = $cacheItem->get();

        if (self::DOMAIN_LOOKUP_CACHE_MISS === $cachedValue) {
            return null;
        }

        if (\is_array($cachedValue)) {
            $cachedValue = $cachedValue['id'] ?? null;

            if (!\is_string($cachedValue) || '' === $cachedValue) {
                $this->cache->deleteItem($cacheItem->getKey());

                return false;
            }
        }

        if (!\is_string($cachedValue) || '' === $cachedValue) {
            $this->cache->deleteItem($cacheItem->getKey());

            return false;
        }

        $channel = $this->normalizeChannel($this->find($cachedValue));

        if (null !== $channel) {
            return $channel;
        }

        $this->cache->deleteItem($cacheItem->getKey());

        return false;
    }

    private function saveDomainToPersistentCache(string $domain, ?ChannelInterface $channel): void
    {
        if (null === $this->cache) {
            return;
        }

        $cacheItem = $this->cache->getItem($this->getDomainLookupCacheKey($domain));
        $cacheItem->set($channel ? $channel->getId() : self::DOMAIN_LOOKUP_CACHE_MISS);
        $cacheItem->expiresAfter(self::DOMAIN_LOOKUP_CACHE_TTL_SECONDS);
        $this->cache->save($cacheItem);
    }

    private function getDomainLookupCacheKey(string $domain): string
    {
        return self::DOMAIN_LOOKUP_CACHE_KEY_PREFIX.$this->getDomainLookupCacheVersion().'_'.md5($domain);
    }

    private function getDomainLookupCacheVersion(): string
    {
        if (null !== $this->domainLookupCacheVersion) {
            return $this->domainLookupCacheVersion;
        }

        if (null === $this->cache) {
            $this->domainLookupCacheVersion = self::DOMAIN_LOOKUP_CACHE_VERSION_DEFAULT;

            return $this->domainLookupCacheVersion;
        }

        $cacheItem = $this->cache->getItem(self::DOMAIN_LOOKUP_CACHE_VERSION_KEY);

        if ($cacheItem->isHit()) {
            $cachedValue = $cacheItem->get();

            if (\is_string($cachedValue) && '' !== $cachedValue) {
                $this->domainLookupCacheVersion = $cachedValue;

                return $this->domainLookupCacheVersion;
            }

            $this->cache->deleteItem($cacheItem->getKey());
        }

        $cacheItem->set(self::DOMAIN_LOOKUP_CACHE_VERSION_DEFAULT);
        $this->cache->save($cacheItem);

        $this->domainLookupCacheVersion = self::DOMAIN_LOOKUP_CACHE_VERSION_DEFAULT;

        return $this->domainLookupCacheVersion;
    }

    private function bumpDomainLookupCacheVersion(): string
    {
        $version = bin2hex(random_bytes(8));

        if (null !== $this->cache) {
            $cacheItem = $this->cache->getItem(self::DOMAIN_LOOKUP_CACHE_VERSION_KEY);
            $cacheItem->set($version);
            $this->cache->save($cacheItem);
        }

        return $version;
    }

    private function getFallbackDomain(string $domain): ?string
    {
        if (!str_contains($domain, '.')) {
            return null;
        }

        if (str_starts_with($domain, 'www.')) {
            $fallbackDomain = substr($domain, 4);

            return '' !== $fallbackDomain ? $fallbackDomain : null;
        }

        return 'www.'.$domain;
    }

    private function normalizeChannel(mixed $channel): ?ChannelInterface
    {
        return $channel instanceof ChannelInterface ? $channel : null;
    }
}
