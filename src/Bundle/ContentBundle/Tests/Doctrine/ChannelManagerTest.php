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

final class ChannelManagerTest extends TestCase
{
    public function testFindByDomainUsesWwwFallbackWithoutDoublingPrefix(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        /** @var ObjectRepository&MockObject $repository */
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

        /** @var ObjectRepository&MockObject $repository */
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
        /** @var ObjectRepository&MockObject $repository */
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

    /**
     * @param ObjectRepository&MockObject $repository
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
