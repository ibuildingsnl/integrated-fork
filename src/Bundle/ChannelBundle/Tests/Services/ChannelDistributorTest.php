<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Services;

use Integrated\Bundle\ChannelBundle\Services\ChannelDistributor;
use Integrated\Bundle\ChannelBundle\Services\DistributionQueue;
use Integrated\Bundle\ChannelBundle\Tests\Mock\MemoryPublicationRepository;
use Integrated\Bundle\ChannelBundle\Tests\Mock\Serializer;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Common\Channel\Exporter\Queue\Request;
use Integrated\Common\Content\PublishTimeInterface;
use Integrated\Common\Queue\Provider\Memory\QueueProvider;
use Integrated\Common\Queue\Queue;
use Integrated\Common\Test\Fixture\ArticleMother;
use PHPUnit\Framework\TestCase;
use Stratadox\Clock\RewindableDateTimeClock;
use Stratadox\Clock\SneakyTestClock;
use Stratadox\Clock\UnmovingClock;

class ChannelDistributorTest extends TestCase
{
    private ChannelDistributor $channelDistributor;
    private DistributionQueue $queue;
    private SneakyTestClock $clock;
    private ArticleMother $articleMother;

    protected function setUp(): void
    {
        $this->clock = SneakyTestClock::create();
        $this->queue = new DistributionQueue(
            new Queue(new QueueProvider($this->clock), 'channel-distribution'),
            new Serializer(),
        );
        $publications = new MemoryPublicationRepository();
        $this->channelDistributor = new ChannelDistributor($this->queue, $publications, $this->clock);
        $this->articleMother = new ArticleMother($publications);
    }

    public function testDoNothingForContentWithoutChannels()
    {
        $this->channelDistributor->distribute($this->articleMother->withoutChannels());

        self::assertEmpty($this->queue);
    }

    public function testAddContentWithOneChannelToQueue()
    {
        $this->channelDistributor->distribute($this->articleMother->withChannel());

        self::assertNotEmpty($this->queue);
        $message = $this->queue->pull(1)[0];
        self::assertInstanceOf(Request::class, $message);
        self::assertEquals('add', $message->state);
    }

    public function testAddContentWithScheduledPublicationToQueueWithDelay()
    {
        $this->channelDistributor->distribute($this->articleMother->withPublication(
            $this->clock->fastForward(\DateInterval::createFromDateString('+10 hour'))->now()
        ));

        self::assertEmpty($this->queue);
    }

    public function testSeeingContentWithScheduledPublicationInQueueAfterDelay()
    {
        $this->channelDistributor->distribute($this->articleMother->withPublication(
            $this->clock->fastForward(\DateInterval::createFromDateString('+10 hour'))->now(),
        ));

        $this->clock->sneakForwards(\DateInterval::createFromDateString('+10 hour'));

        self::assertNotEmpty($this->queue);
    }

    public function testPublishingToDifferentChannelsOnDifferentTimes()
    {
        $this->channelDistributor->distribute($this->articleMother->withPublications(
            $this->clock->fastForward(\DateInterval::createFromDateString('+1 hour'))->now(),
            $this->clock->fastForward(\DateInterval::createFromDateString('+22 hour'))->now(),
        ));

        $this->clock->sneakForwards(\DateInterval::createFromDateString('+10 hour'));

        self::assertCount(1, $this->queue);
    }

    public function testRemovingUnpublishedMaterial()
    {
        $this->channelDistributor->distribute($this->articleMother->disabled());

        self::assertNotEmpty($this->queue);
        $message = $this->queue->pull(1)[0];
        self::assertInstanceOf(Request::class, $message);
        self::assertEquals('delete', $message->state);
    }

    public function testSchedulingRemovalAtEndDate()
    {
        $this->channelDistributor->distribute($this->articleMother->withPublication(
            (new PublishTime())->setStartDate($this->clock->now())->setEndDate(
                $this->clock->fastForward(\DateInterval::createFromDateString('+10 hour'))->now(),
            ),
        ));

        $this->clock->sneakForwards(\DateInterval::createFromDateString('+10 hour'));

        self::assertCount(2, $this->queue);
        $message = $this->queue->pull(2)[1];
        self::assertInstanceOf(Request::class, $message);
        self::assertEquals('delete', $message->state);
    }

    public function testNotSchedulingRemovalForMaxEndDate()
    {
        $this->channelDistributor->distribute($this->articleMother->withPublication(
            (new PublishTime())->setStartDate($this->clock->now())->setEndDate(
                new \DateTime(PublishTimeInterface::DATE_MAX),
            ),
        ));

        $this->clock->sneakilySetClock(RewindableDateTimeClock::using(UnmovingClock::standingStillAt(
            new \DateTime(PublishTimeInterface::DATE_MAX)
        )));

        self::assertCount(1, $this->queue);
        $message = $this->queue->pull(1)[0];
        self::assertNotEquals('delete', $message->state);
    }

    public function testSchedulingImmediateRemovalForDeletions()
    {
        $this->channelDistributor->delete($this->articleMother->withChannel());

        self::assertCount(1, $this->queue);
        $message = $this->queue->pull(1)[0];
        self::assertEquals('delete', $message->state);
    }

    public function testNotSchedulingRemovalsForDeletionWithoutChannels()
    {
        $this->channelDistributor->delete($this->articleMother->withoutChannels());

        self::assertEmpty($this->queue);
    }

    public function testSchedulingMultipleRemovalsForDeletionWithMultipleChannels()
    {
        $this->channelDistributor->delete($this->articleMother->withPublications(
            (new PublishTime())->setStartDate(
                $this->clock->fastForward(\DateInterval::createFromDateString('+10 hours'))->now(),
            ),
            (new PublishTime())->setStartDate(
                $this->clock->fastForward(\DateInterval::createFromDateString('+88 days'))->now(),
            ),
        ));

        self::assertCount(2, $this->queue);
        $message = $this->queue->pull(2);
        self::assertEquals('delete', $message[0]->state);
        self::assertEquals('delete', $message[1]->state);
    }

    public function testPublishingWithPublicationSettings()
    {
        $this->channelDistributor->distribute($this->articleMother->withPublication(
            $this->clock->fastForward(\DateInterval::createFromDateString('+10 hour'))->now(),
            null,
            ['foo' => 'bar'],
        ));

        $this->clock->sneakForwards(\DateInterval::createFromDateString('+10 hour'));

        self::assertNotEmpty($this->queue);
        $message = $this->queue->pull(1)[0];
        self::assertEquals('add', $message->state);
        self::assertEquals(['foo' => 'bar'], $message->settings);
    }
}
