<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Provider;

use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;

final class AvailableConnectorsProvider
{
    public function __construct(
        private readonly RegistryInterface $adapterRegistry,
    ) {
    }

    /**
     * @return string[]
     */
    public function getAvailableConnectors(): array
    {
        $available = [];

        foreach ($this->adapterRegistry->getAdapters() as $adapter) {
            $available[] = $adapter->getManifest()->getName();
        }

        return array_values(array_unique($available));
    }
}
