<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\EventListener;

use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Bundle\WebsiteBundle\EventListener\WebsiteConfigurationListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class WebsiteConfigurationListenerTest extends TestCase
{
    public function testSubscriberRunsBeforeRouterListener(): void
    {
        $events = WebsiteConfigurationListener::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        self::assertSame('onKernelRequest', $events[KernelEvents::REQUEST][0]);
        self::assertSame(33, $events[KernelEvents::REQUEST][1]);
    }

    public function testOnKernelRequestSetsThemeForMainRequestWhenChannelExists(): void
    {
        /** @var ChannelInterface&MockObject $channel */
        $channel = $this->createMock(ChannelInterface::class);

        /** @var ChannelContextInterface&MockObject $context */
        $context = $this->createMock(ChannelContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        /** @var ThemeResolver&MockObject $resolver */
        $resolver = $this->createMock(ThemeResolver::class);
        $resolver
            ->expects($this->once())
            ->method('getTheme')
            ->with($channel)
            ->willReturn('twindigital');

        /** @var ThemeManager&MockObject $themeManager */
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects($this->once())
            ->method('setActiveTheme')
            ->with('twindigital');

        $listener = new WebsiteConfigurationListener($context, $themeManager, $resolver);
        $listener->onKernelRequest($this->createEvent(HttpKernelInterface::MAIN_REQUEST));
    }

    public function testOnKernelRequestSkipsSubRequests(): void
    {
        /** @var ChannelContextInterface&MockObject $context */
        $context = $this->createMock(ChannelContextInterface::class);
        $context
            ->expects($this->never())
            ->method('getChannel');

        /** @var ThemeResolver&MockObject $resolver */
        $resolver = $this->createMock(ThemeResolver::class);
        $resolver
            ->expects($this->never())
            ->method('getTheme');

        /** @var ThemeManager&MockObject $themeManager */
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects($this->never())
            ->method('setActiveTheme');

        $listener = new WebsiteConfigurationListener($context, $themeManager, $resolver);
        $listener->onKernelRequest($this->createEvent(HttpKernelInterface::SUB_REQUEST));
    }

    private function createEvent(int $requestType): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.test/blogs/foo/bar'),
            $requestType
        );
    }
}
