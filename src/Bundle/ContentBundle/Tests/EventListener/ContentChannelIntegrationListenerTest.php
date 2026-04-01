<?php

namespace Integrated\Bundle\ContentBundle\Tests\EventListener;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\EventListener\ContentChannelIntegrationListener;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContentChannelIntegrationListenerTest extends TestCase
{
    public function testGetChannelsLoadsAllChannelsWhenIdsAreOmitted(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $expected = [$channel];

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expected);
        $repository
            ->expects($this->never())
            ->method('findBy');

        $listener = new TestableContentChannelIntegrationListener(
            $repository,
            $this->createMock(AuthorizationCheckerInterface::class)
        );

        self::assertSame($expected, $listener->exposedGetChannels());
    }

    public function testGetChannelsReturnsEmptyListWithoutQueryForEmptyIds(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->never())
            ->method('findAll');
        $repository
            ->expects($this->never())
            ->method('findBy');

        $listener = new TestableContentChannelIntegrationListener(
            $repository,
            $this->createMock(AuthorizationCheckerInterface::class)
        );

        self::assertSame([], $listener->exposedGetChannels([]));
    }

    public function testGetChannelsBuildsOrCriteriaForSpecificIds(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $expected = [$channel];

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->never())
            ->method('findAll');
        $repository
            ->expects($this->once())
            ->method('findBy')
            ->with(['$or' => [['id' => 'a'], ['id' => 'b']]])
            ->willReturn($expected);

        $listener = new TestableContentChannelIntegrationListener(
            $repository,
            $this->createMock(AuthorizationCheckerInterface::class)
        );

        self::assertSame($expected, $listener->exposedGetChannels(['a', 'b']));
    }
}

final class TestableContentChannelIntegrationListener extends ContentChannelIntegrationListener
{
    /**
     * @param array<int, string>|null $ids
     *
     * @return array<int, \Integrated\Common\Content\Channel\ChannelInterface>
     */
    public function exposedGetChannels(?array $ids = null): array
    {
        return $this->getChannels($ids);
    }
}
