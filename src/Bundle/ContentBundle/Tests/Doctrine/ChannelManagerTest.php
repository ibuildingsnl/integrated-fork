<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Doctrine\ChannelManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class ChannelManagerTest extends TestCase
{
    public function testFindByDomainUsesWwwFallbackWithoutDoublingPrefix(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        /** @var ObjectRepository<Channel>&MockObject $repository */
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Channel::class);
        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnCallback(function (array $criteria) use ($channel) {
                static $call = 0;
                ++$call;

                if (1 === $call) {
                    self::assertSame(['domains' => 'www.example.com'], $criteria);

                    return null;
                }

                self::assertSame(['domains' => 'example.com'], $criteria);

                return $channel;
            });

        $manager = $this->createManager($repository);

        self::assertSame($channel, $manager->findByDomain('www.example.com'));
        self::assertSame($channel, $manager->findByDomain('www.example.com'));
    }

    public function testFindByDomainUsesWwwFallbackForBareDomain(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        /** @var ObjectRepository<Channel>&MockObject $repository */
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Channel::class);
        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnCallback(function (array $criteria) use ($channel) {
                static $call = 0;
                ++$call;

                if (1 === $call) {
                    self::assertSame(['domains' => 'example.com'], $criteria);

                    return null;
                }

                self::assertSame(['domains' => 'www.example.com'], $criteria);

                return $channel;
            });

        $manager = $this->createManager($repository);

        self::assertSame($channel, $manager->findByDomain('example.com'));
    }

    public function testFindByDomainSkipsFallbackForLocalhostStyleHosts(): void
    {
        /** @var ObjectRepository<Channel>&MockObject $repository */
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Channel::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['domains' => 'localhost'])
            ->willReturn(null);

        $manager = $this->createManager($repository);

        self::assertNull($manager->findByDomain('localhost'));
    }

    public function testFindByDomainUsesPersistentCacheAcrossManagerInstances(): void
    {
        $channel = new Channel();
        $channel->setId('channel-id');
        $channel->setName('Example Channel');
        $channel->setPrimaryDomain('example.com');
        $channel->setPrimaryDomainRedirect(true);

        /** @var ObjectRepository<Channel>&MockObject $repository */
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->exactly(2))
            ->method('getClassName')
            ->willReturn(Channel::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['domains' => 'example.com'])
            ->willReturn($channel);
        $repository
            ->expects($this->once())
            ->method('find')
            ->with('channel-id')
            ->willReturn($channel);

        $cache = new ArrayAdapter();

        /** @var ObjectManager&MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($repository);

        $managerOne = new ChannelManager($objectManager, Channel::class, $cache);
        $managerTwo = new ChannelManager($objectManager, Channel::class, $cache);

        self::assertSame($channel, $managerOne->findByDomain('example.com'));

        $cached = $managerTwo->findByDomain('example.com');
        self::assertNotNull($cached);
        self::assertSame($channel, $cached);
    }

    public function testFindByDomainCachesMissesAcrossManagerInstances(): void
    {
        /** @var ObjectRepository<Channel>&MockObject $repository */
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->exactly(2))
            ->method('getClassName')
            ->willReturn(Channel::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['domains' => 'localhost'])
            ->willReturn(null);
        $repository
            ->expects($this->never())
            ->method('find');

        $cache = new ArrayAdapter();

        /** @var ObjectManager&MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($repository);

        $managerOne = new ChannelManager($objectManager, Channel::class, $cache);
        $managerTwo = new ChannelManager($objectManager, Channel::class, $cache);

        self::assertNull($managerOne->findByDomain('localhost'));
        self::assertNull($managerTwo->findByDomain('localhost'));
    }

    public function testInvalidateDomainLookupCacheForcesFreshLookup(): void
    {
        $firstChannel = $this->createMock(ChannelInterface::class);

        $secondChannel = $this->createMock(ChannelInterface::class);

        /** @var ObjectRepository<Channel>&MockObject $repository */
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('getClassName')
            ->willReturn(Channel::class);
        $repository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->with(['domains' => 'example.com'])
            ->willReturnOnConsecutiveCalls($firstChannel, $secondChannel);

        $cache = new ArrayAdapter();

        /** @var ObjectManager&MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($repository);

        $manager = new ChannelManager($objectManager, Channel::class, $cache);

        self::assertSame($firstChannel, $manager->findByDomain('example.com'));

        $manager->invalidateDomainLookupCache();

        self::assertSame($secondChannel, $manager->findByDomain('example.com'));
    }

    /**
     * @param ObjectRepository<Channel>&MockObject $repository
     */
    private function createManager(ObjectRepository $repository): ChannelManager
    {
        /** @var ObjectManager&MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($repository);

        return new ChannelManager($objectManager, Channel::class);
    }
}
