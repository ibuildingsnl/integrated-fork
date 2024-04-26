<?php

namespace Integrated\Bundle\AnalyticsBundle\Document;

class AnalyticsData
{
    private readonly string $id;
    private string $channelID;
    private string $dataType;
    private array $datas;
    private \DateTimeImmutable $dateTime;

    public function __construct(
        string $channelID,
        string $dataType,
        array $datas,
        \DateTimeImmutable $dateTime
    ) {
        $this->channelID = $channelID;
        $this->datas = $datas;
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

    public function getDatas(): array
    {
        return $this->datas;
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }
}
