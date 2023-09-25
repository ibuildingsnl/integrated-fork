<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Common\Channel\Exporter\Queue\Request;
use Integrated\Common\Content\ChannelableInterface;

class ChannelDistributor
{
    public function __construct(
        private readonly DistributionQueue $queue,
    ) {
    }

    public function distribute(ChannelableInterface $content): void
    {
        foreach ($content->getChannels() as $channel) {
            // @todo
            $this->queue->push(new Request($content, 'add', $channel), 0);
        }
    }
}
