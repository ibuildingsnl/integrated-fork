<?php

namespace Integrated\Bundle\AnalyticsBundle\Document;

class AnalyticsData
{
    private readonly string $id;
    private string $channelID;
    private string $dataType;
    private array $data;
    private \DateTimeImmutable $dateTime;

    public function __construct(
        string $channelID,
        string $dataType,
        array $data,
        \DateTimeImmutable $dateTime
    ) {
        $this->channelID = $channelID;
        $this->data = $data;
        $this->dataType = $dataType;
        $this->dateTime = $dateTime;
    }

    public function getChannelID(): string
    {
        return $this->channelID;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }
}
