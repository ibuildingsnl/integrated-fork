<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Provider;

use Integrated\Bundle\BrandBundle\Provider\AvailableConnectorsProvider;
use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\AdapterInterface;
use PHPUnit\Framework\TestCase;

final class AvailableConnectorsProviderTest extends TestCase
{
    public function testGetAvailableConnectorsReturnsUniqueConnectorNames(): void
    {
        $registry = $this->createMock(RegistryInterface::class);
        $registry->expects(self::once())
            ->method('getAdapters')
            ->willReturn([
                $this->createAdapter('mailchimp'),
                $this->createAdapter('spotler'),
                $this->createAdapter('mailchimp'),
            ]);

        $provider = new AvailableConnectorsProvider($registry);

        self::assertSame(['mailchimp', 'spotler'], $provider->getAvailableConnectors());
    }

    private function createAdapter(string $name): AdapterInterface
    {
        $manifest = $this->createMock(ManifestInterface::class);
        $manifest->expects(self::once())
            ->method('getName')
            ->willReturn($name);

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->expects(self::once())
            ->method('getManifest')
            ->willReturn($manifest);

        return $adapter;
    }
}
