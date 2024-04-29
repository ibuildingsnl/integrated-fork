<?php

namespace Integrated\Bundle\AnalyticsBundle\Document;

class SitePerformance
{
    private string $id;
    private string $channelID;

    private float $desktopSpeedIndex;
    private float $desktopSiteScore;
    private float $desktopTimeToInteractive;
    private float $desktopServerResponseTime;
    private float $desktopTotalBlockingTime;

    private float $mobileSpeedIndex;
    private float $mobileSiteScore;
    private float $mobileTimeToInteractive;
    private float $mobileServerResponseTime;
    private float $mobileTotalBlockingTime;

    private \DateTimeImmutable $dateTime;

    public function __construct(
        string $channelID,
        float $desktopSiteScore,
        float $desktopSpeedIndex,
        float $desktopTimeToInteractive,
        float $desktopServerResponseTime,
        float $desktopTotalBlockingTime,
        float $mobileSiteScore,
        float $mobileSpeedIndex,
        float $mobileTimeToInteractive,
        float $mobileServerResponseTime,
        float $mobileTotalBlockingTime,
        \DateTimeImmutable $dateTime
    ) {
        $this->channelID = $channelID;
        $this->desktopSpeedIndex = $desktopSpeedIndex;
        $this->desktopSiteScore = $desktopSiteScore;
        $this->desktopTimeToInteractive = $desktopTimeToInteractive;
        $this->desktopServerResponseTime = $desktopServerResponseTime;
        $this->desktopTotalBlockingTime = $desktopTotalBlockingTime;
        $this->mobileSpeedIndex = $mobileSpeedIndex;
        $this->mobileSiteScore = $mobileSiteScore;
        $this->mobileTimeToInteractive = $mobileTimeToInteractive;
        $this->mobileServerResponseTime = $mobileServerResponseTime;
        $this->mobileTotalBlockingTime = $mobileTotalBlockingTime;
        $this->dateTime = $dateTime;
    }

    public function getChannelID(): string
    {
        return $this->channelID;
    }

    public function getDesktopSiteScore(): float
    {
        return $this->desktopSiteScore;
    }

    public function getDesktopSpeedIndex(): float
    {
        return $this->desktopSpeedIndex;
    }

    public function getDesktopTimeToInteractive(): float
    {
        return $this->desktopTimeToInteractive;
    }

    public function getDesktopServerResponseTime(): float
    {
        return $this->desktopServerResponseTime;
    }

    public function getDesktopTotalBlockingTime(): float
    {
        return $this->desktopTotalBlockingTime;
    }

    public function getMobileSiteScore(): float
    {
        return $this->mobileSiteScore;
    }

    public function getMobileSpeedIndex(): float
    {
        return $this->mobileSpeedIndex;
    }

    public function getMobileTimeToInteractive(): float
    {
        return $this->mobileTimeToInteractive;
    }

    public function getMobileServerResponseTime(): float
    {
        return $this->mobileServerResponseTime;
    }

    public function getMobileTotalBlockingTime(): float
    {
        return $this->mobileTotalBlockingTime;
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }
}
