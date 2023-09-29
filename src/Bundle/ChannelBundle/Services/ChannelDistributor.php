<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Exporter\Queue\Request;
use Integrated\Common\Content\PublishTimeInterface;
use Stratadox\Clock\Clock;

class ChannelDistributor
{
    private static \DateTimeInterface $maxDate;

    public function __construct(
        private readonly DistributionQueue $queue,
        private readonly PublicationRepositoryInterface $publications,
        private readonly Clock $clock,
    ) {
    }

    public function distribute(Content $content): void
    {
        foreach ($content->getChannels() as $channel) {
            $this->distributeTo($channel, $content, ...$this->publications->forContentOnChannel($content, $channel));
        }
    }

    private function distributeTo(ChannelInterface $channel, Content $content, Publication $publication = null): void
    {
        if ($content->isDisabled()) {
            $this->push($content, $channel, false, null);
            return;
        }
        $this->scheduleDistributionWindow(
            $channel,
            $content,
            $publication ? $publication->getTime() : $content->getPublishTime()
        );
    }

    private function scheduleDistributionWindow(ChannelInterface $channel, Content $content, PublishTimeInterface $window): void
    {
        $this->push($content, $channel, true, $window->getStartDate());

        if ($window->getEndDate() && $window->getEndDate() < self::maxDate()) {
            $this->push($content, $channel, false, $window->getEndDate());
        }
    }

    private function push(Content $content, ChannelInterface $channel, bool $add, ?\DateTimeInterface $when): void
    {
        $this->queue->push(
            new Request($content, $add ? 'add' : 'delete', $channel),
            $this->secondsUntil($when, $this->clock->now()),
        );
    }

    private function secondsUntil(?\DateTimeInterface $then, \DateTimeInterface $now): int
    {
        return max(0, $then?->getTimestamp() - $now->getTimestamp());
    }

    private static function maxDate(): \DateTimeInterface
    {
        if (!isset(self::$maxDate)) {
            self::$maxDate = new \DateTimeImmutable(PublishTimeInterface::DATE_MAX);
        }
        return self::$maxDate;
    }
}
