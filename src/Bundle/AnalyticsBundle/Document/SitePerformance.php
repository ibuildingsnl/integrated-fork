<?php

namespace Integrated\Bundle\AnalyticsBundle\Document;

class SitePerformance
{
    private readonly string $id;

    public function __construct(
        private readonly string $channelID,
        private readonly float $speed,
        private readonly \DateTimeImmutable $dateTime,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getChannel(): string
    {
        return $this->channelID;
    }

    public function getSiteSpeed(): float
    {
        return $this->speed;
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }
}
