<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Services;

use Integrated\Bundle\ChannelBundle\Services\ChannelDistributor;
use Integrated\Bundle\ChannelBundle\Services\DistributionQueue;
use Integrated\Bundle\ChannelBundle\Tests\Mock\Serializer;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\Exporter\Queue\Request;
use Integrated\Common\Queue\Provider\Memory\QueueProvider;
use Integrated\Common\Queue\Queue;
use Integrated\Common\Queue\QueueMessageInterface;
use Integrated\Common\Test\Fixture\ArticleMother;
use PHPUnit\Framework\TestCase;

class ChannelDistributorTest extends TestCase
{
    private ChannelDistributor $channelDistributor;
    private DistributionQueue $queue;
    private PublicationRepositoryInterface $publications;

    protected function setUp(): void
    {
        $this->queue = new DistributionQueue(
            new Queue(new QueueProvider(), 'channel-distribution'),
            new Serializer(),
        );

        $this->channelDistributor = new ChannelDistributor($this->queue);
    }

    public function testDoNothingForContentWithoutChannels()
    {
        $this->channelDistributor->distribute(ArticleMother::withoutChannels());

        self::assertEmpty($this->queue);
    }

    public function testAddContentWithOneChannelToQueue()
    {
        $this->channelDistributor->distribute(ArticleMother::withChannel());

        self::assertNotEmpty($this->queue);
        self::assertInstanceOf(Request::class, $this->queue->pull(1)[0]);
    }

//    public function testAddContentWithScheduledPublicationToQueueWithDelay()
//    {
//        // @todo make delay work in queue
//    }
}
