<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Common\Content\Channel\ChannelInterface;

class ChannelRepository
{
    private readonly ObjectRepository $websiteChannels;
    private readonly ObjectRepository $secondaryChannels;

    public function __construct(
        private readonly DocumentManager $manager
    ) {
        $this->websiteChannels = $this->manager->getRepository(WebsiteChannel::class);
        $this->secondaryChannels = $this->manager->getRepository(SecondaryChannel::class);
    }

    public function add(ChannelInterface $channel): void
    {
        $this->manager->persist($channel);
    }

    public function remove(ChannelInterface $channel): void
    {
        $this->manager->remove($channel);
    }

    public function find(string $id): ?ChannelInterface
    {
        return $this->websiteChannels->find($id) ?: $this->secondaryChannels->find($id);
    }

    public function findAll(): array
    {
        return array_merge(
            $this->websiteChannels->findAll(),
            $this->secondaryChannels->findAll(),
        );
    }

    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
        // @todo fix sorting
        return array_merge(
            $this->websiteChannels->findBy($criteria, $orderBy, $limit, $offset),
            $this->secondaryChannels->findBy($criteria, $orderBy, $limit, $offset),
        );
    }

    /**
     * @return ChannelInterface[]
     */
    public function findByIds(array $ids): array
    {
        // @todo test
        return array_merge(
            $this->websiteChannels->findBy(['id' => $ids]),
            $this->secondaryChannels->findBy(['id' => $ids]),
        );
    }
}
