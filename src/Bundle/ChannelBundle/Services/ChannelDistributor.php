<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\Exporter\Queue\Request;
use Integrated\Common\Content\Channel\ChannelInterface;
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
            $publications = $this->publications->forContentOnChannel($content, $channel);
            foreach ($publications as $publication) {
                $this->distributeTo($channel, $content, $publication);
            }
        }
    }

    public function delete(Content $content): void
    {
        foreach ($content->getChannels() as $channel) {
            $this->push($content, $channel, false, null);
        }
    }

    private function distributeTo(ChannelInterface $channel, Content $content, Publication $publication = null): void
    {
        if ($content->isDisabled()) {
            $this->push($content, $channel, false);

            return;
        }
        if ($publication->getStatus() !== 'success') {
            $this->scheduleDistributionWindow(
                $channel,
                $content,
                $publication ? $publication->getTime() : $content->getPublishTime(),
                $publication?->getSettings() ?: []
            );
        }
    }

    private function scheduleDistributionWindow(
        ChannelInterface $channel,
        Content $content,
        PublishTimeInterface $window,
        array $settings,
    ): void {
        $this->push($content, $channel, true, $window->getStartDate(), $settings);

        if ($window->getEndDate() && $window->getEndDate() < self::maxDate()) {
            $this->push($content, $channel, false, $window->getEndDate(), $settings);
        }
    }

    private function push(
        Content $content,
        ChannelInterface $channel,
        bool $add,
        ?\DateTimeInterface $when = null,
        array $settings = []
    ): void {
        $this->queue->push(
            new Request($content, $add ? 'add' : 'delete', $channel, $settings),
            max(0, $when?->getTimestamp() - $this->clock->now()->getTimestamp()),
        );
    }

    private static function maxDate(): \DateTimeInterface
    {
        if (!isset(self::$maxDate)) {
            self::$maxDate = new \DateTimeImmutable(PublishTimeInterface::DATE_MAX);
        }

        return self::$maxDate;
    }
}
