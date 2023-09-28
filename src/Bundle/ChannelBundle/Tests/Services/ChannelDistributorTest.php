<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Services;

use Integrated\Bundle\ChannelBundle\Services\ChannelDistributor;
use Integrated\Bundle\ChannelBundle\Services\DistributionQueue;
use Integrated\Bundle\ChannelBundle\Tests\Mock\MemoryPublicationRepository;
use Integrated\Bundle\ChannelBundle\Tests\Mock\Serializer;
use Integrated\Common\Channel\Exporter\Queue\Request;
use Integrated\Common\Queue\Provider\Memory\QueueProvider;
use Integrated\Common\Queue\Queue;
use Integrated\Common\Test\Fixture\ArticleMother;
use PHPUnit\Framework\TestCase;
use Stratadox\Clock\SneakyTestClock;

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
        self::assertInstanceOf(Request::class, $this->queue->pull(1)[0]);
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
}
