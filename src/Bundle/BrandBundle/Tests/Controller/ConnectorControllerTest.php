<?php

namespace Integrated\Bundle\BrandBundle\Tests\Controller;

use Integrated\Bundle\BrandBundle\Controller\ConnectorController;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Provider\ConnectorMissingThemeBlocksProvider;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ConfigManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConnectorControllerTest extends TestCase
{
    public function testConfigureThrowsNotFoundIfLinkDoesNotBelongToBrand(): void
    {
        $configs = $this->createMock(ConfigManagerInterface::class);
        $configs->expects(self::never())->method('findByChannel');

        $controller = new TestableConnectorController(
            $configs,
            $this->createMock(RegistryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(ConnectorMissingThemeBlocksProvider::class),
        );

        $brand = new Brand();
        $brand->addChannelLink(new ChannelLink(new ChannelType('website', 'Website'), null, false));
        $unknownLink = new ChannelLink(new ChannelType('newsletter', 'Newsletter'), null, false);

        $this->expectException(NotFoundHttpException::class);
        $controller->configure(Request::create('/'), $brand, $unknownLink);
    }
}

class TestableConnectorController extends ConnectorController
{
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return true;
    }
}
