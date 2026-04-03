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

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ChannelManager implements ChannelManagerInterface
{
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

    public function __construct(ObjectManager $om, $class)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository($class);

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
        return $this->repository->find($id);
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

        $channel = $this->repository->findOneBy(['domains' => $domain]);

        if (!$channel) {
            $fallbackDomain = $this->getFallbackDomain($domain);

            if (null !== $fallbackDomain) {
                $channel = $this->repository->findOneBy(['domains' => $fallbackDomain]);
                $this->domainLookupCache[$fallbackDomain] = $channel;
            }
        }

        $this->domainLookupCache[$domain] = $channel;

        return $channel;
    }

    public function findByName($criteria)
    {
        return $this->repository->findOneBy(['shortName' => $criteria]);
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
}
