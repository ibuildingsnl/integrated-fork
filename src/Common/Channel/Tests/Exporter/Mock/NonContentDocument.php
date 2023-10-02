<?php

namespace Integrated\Common\Channel\Tests\Exporter\Mock;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Common\Content\ConnectableInterface;
use Integrated\Common\Content\ConnectorTrait;

class NonContentDocument implements ConnectableInterface
{
    use ConnectorTrait;

    public function __construct()
    {
        $this->connectors = new ArrayCollection();
    }
}
