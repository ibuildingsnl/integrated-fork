<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Exporter\Queue\Request;
use Stratadox\Clock\Clock;

class ChannelDistributor
{
    public function __construct(
        private readonly DistributionQueue $queue,
        private readonly PublicationRepositoryInterface $publications,
        private readonly Clock $clock,
    ) {
    }

    public function distribute(Content $content): void
    {
        foreach ($content->getChannels() as $channel) {
            $this->distributeTo($channel, $content, $this->publications->forContentOnChannel($content, $channel));
        }
    }

    private function distributeTo(ChannelInterface $channel, Content $content): void
    {
        $this->queue->push(new Request($content, 'add', $channel), $this->secondsUntil(
            $content->getPublishTime()->getStartDate(), // @todo use publication
            $this->clock->now(),
        ));
    }

    private function secondsUntil(?\DateTimeInterface $then, \DateTimeInterface $now): int
    {
        if (!$then || $then < $now) {
            return 0;
        }
        return $then->getTimestamp() - $now->getTimestamp();
    }
}
