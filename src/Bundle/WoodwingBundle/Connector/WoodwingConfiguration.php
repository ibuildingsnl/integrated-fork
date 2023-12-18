<?php

namespace Integrated\Bundle\WoodwingBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorConfigInterface;

final class WoodwingConfiguration implements ConnectorConfigInterface
{
    public function getName(): string
    {
        return WoodwingConnector::NAME;
    }

    public function getForm(): string
    {
        return WoodwingConfigType::class;
    }
}
