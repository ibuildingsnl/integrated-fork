<?php

namespace Integrated\Bundle\BrandBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BrandBundle\Controller\ChannelLinkController;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Provider\AvailableConnectorsProvider;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Infrastructure\ChannelTypeRegistry;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Services\Flusher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelLinkControllerTest extends TestCase
{
    public function testEditChannelThrowsNotFoundIfLinkDoesNotBelongToBrand(): void
    {
        $controller = new TestableChannelLinkController(
            $this->createMock(ChannelTypeRegistry::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(Flusher::class),
            $this->createMock(DocumentManager::class),
            $this->createAvailableConnectorsProvider(),
        );

        $brand = new Brand();
        $brand->addChannelLink(new ChannelLink(new ChannelType('website', 'Website'), null, false));
        $unknownLink = new ChannelLink(new ChannelType('newsletter', 'Newsletter'), null, false);

        $this->expectException(NotFoundHttpException::class);
        $controller->editChannel(Request::create('/'), $brand, $unknownLink);
    }

    public function testRemoveChannelThrowsNotFoundIfLinkDoesNotBelongToBrand(): void
    {
        $controller = new TestableChannelLinkController(
            $this->createMock(ChannelTypeRegistry::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(Flusher::class),
            $this->createMock(DocumentManager::class),
            $this->createAvailableConnectorsProvider(),
        );

        $brand = new Brand();
        $brand->addChannelLink(new ChannelLink(new ChannelType('website', 'Website'), null, false));
        $unknownLink = new ChannelLink(new ChannelType('newsletter', 'Newsletter'), null, false);

        $this->expectException(NotFoundHttpException::class);
        $controller->removeChannel(Request::create('/'), $brand, $unknownLink);
    }

    private function createAvailableConnectorsProvider(): AvailableConnectorsProvider
    {
        $registry = $this->createMock(RegistryInterface::class);
        $registry->method('getAdapters')->willReturn([]);

        return new AvailableConnectorsProvider($registry);
    }
}

class TestableChannelLinkController extends ChannelLinkController
{
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return true;
    }
}
