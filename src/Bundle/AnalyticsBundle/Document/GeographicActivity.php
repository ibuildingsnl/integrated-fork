<?php

namespace Integrated\Bundle\AnalyticsBundle\Document;

use DateTimeImmutable;


class GeographicActivity
{
    private readonly string $id;
    private string $channelID;
    private array $geographicActivity;
    private DateTimeImmutable $dateTime;

    public function __construct(
        string $channelID,
        array $geographicActivity,
        DateTimeImmutable $dateTime
    ) {
        $this->channelID = $channelID;
        $this->geographicActivity = $geographicActivity;
        $this->dateTime = $dateTime;
    }
    public function getChannelID(): string
    {
        return $this->channelID;
    }
    public function getGeographicActivity(): array
    {
        return $this->geographicActivity;
    }
    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}
