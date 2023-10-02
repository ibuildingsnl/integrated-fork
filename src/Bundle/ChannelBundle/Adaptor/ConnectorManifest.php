<?php

namespace Integrated\Bundle\ChannelBundle\Adaptor;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;

final class ConnectorManifest implements ManifestInterface
{
    public function __construct(
        private readonly ConnectorInterface $connector,
    ) {}

    public function getName(): string
    {
        return $this->connector->getName();
    }

    public function getLabel(): string
    {
        return $this->connector->getName();
    }

    public function getDescription(): string
    {
        return $this->connector->getName();
    }

    public function getVersion(): string
    {
        return '1.0';
    }
}
