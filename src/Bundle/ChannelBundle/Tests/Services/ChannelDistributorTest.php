<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Services;

use Integrated\Bundle\ChannelBundle\Services\ChannelDistributor;
use Integrated\Bundle\ChannelBundle\Services\DistributionQueue;
use Integrated\Bundle\ChannelBundle\Tests\Mock\MemoryPublicationRepository;
use Integrated\Bundle\ChannelBundle\Tests\Mock\Serializer;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
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
    private PublicationRepositoryInterface $publications;

    protected function setUp(): void
    {
        $this->queue = new DistributionQueue(
            new Queue(new QueueProvider(), 'channel-distribution'),
            new Serializer(),
        );
        $this->clock = SneakyTestClock::create();
        $this->publications = new MemoryPublicationRepository();
        $this->channelDistributor = new ChannelDistributor($this->queue, $this->publications, $this->clock);
        $this->articleMother = new ArticleMother($this->publications);
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

        self::assertEmpty($this->queue, \var_export($this->queue->pull(10),1));
    }
}
