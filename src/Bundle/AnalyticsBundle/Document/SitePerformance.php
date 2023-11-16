<?php

namespace Integrated\Bundle\AnalyticsBundle\Document;

use DateTimeImmutable;

class SitePerformance
{
    private readonly string $id;
    private string $channelID;
    private float $speedIndex;
    private float $siteScore;
    private float $timeToInteractive;
    private float $serverResponseTime;
    private float $totalBlockingTime;
    private DateTimeImmutable $dateTime;

    public function __construct(
        string $channelID,
        float $siteScore,
        float $speedIndex,
        float $timeToInteractive,
        float $serverResponseTime,
        float $totalBlockingTime,
        DateTimeImmutable $dateTime,
    ) {
        $this->channelID = $channelID;
        $this->speedIndex = $speedIndex;
        $this->siteScore = $siteScore;
        $this->timeToInteractive = $timeToInteractive;
        $this->serverResponseTime = $serverResponseTime;
        $this->totalBlockingTime = $totalBlockingTime;
        $this->dateTime = $dateTime;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getChannel(): string
    {
        return $this->channelID;
    }

    public function getSiteScore(): float
    {
        return $this->siteScore;
    }

    public function getSpeedIndex(): float
    {
        return $this->speedIndex;
    }

    public function getTimeToInteractive(): float
    {
        return $this->timeToInteractive;
    }

    public function getServerResponseTime(): float
    {
        return $this->serverResponseTime;
    }

    public function getTotalBlockingTime(): float
    {
        return $this->totalBlockingTime;
    }
    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}
