<?php

namespace Integrated\Common\Channel\Exporter;

class ExporterResponse
{
    /**
     * @var int
     */
    protected $configId;

    /**
     * @var string
     */
    protected $configAdapter;

    /**
     * @var string
     */
    protected $externalId;

    public function __construct(int $configId, string $configAdapter)
    {
        $this->configId = $configId;
        $this->configAdapter = $configAdapter;
    }

    public function getConfigId(): int
    {
        return $this->configId;
    }

    public function getConfigAdapter(): string
    {
        return $this->configAdapter;
    }

    public function getExternalId(): string|null
    {
        return $this->externalId;
    }

    /**
     * @return $this
     */
    public function setExternalId(string|null $externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }
}
