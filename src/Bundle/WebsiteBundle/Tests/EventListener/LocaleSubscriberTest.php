<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\EventListener;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\WebsiteBundle\EventListener\LocaleSubscriber;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class LocaleSubscriberTest extends TestCase
{
    public function testSubscriberSetsLocaleFromChannelContextOnWebsiteRequests(): void
    {
        $channel = new Channel();
        $channel->setLanguage('en');

        /** @var ChannelContextInterface&MockObject $context */
        $context = $this->createMock(ChannelContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $subscriber = new LocaleSubscriber($context);
        $request = Request::create('https://example.test/news');

        $subscriber->onKernelRequest($this->createEvent($request, HttpKernelInterface::MAIN_REQUEST));

        self::assertSame('en', $request->getLocale());
    }

    public function testSubscriberSkipsAdminRequests(): void
    {
        /** @var ChannelContextInterface&MockObject $context */
        $context = $this->createMock(ChannelContextInterface::class);
        $context
            ->expects($this->never())
            ->method('getChannel');

        $subscriber = new LocaleSubscriber($context);
        $request = Request::create('https://example.test/admin/content');

        $subscriber->onKernelRequest($this->createEvent($request, HttpKernelInterface::MAIN_REQUEST));

        self::assertSame('en', $request->getLocale());
    }

    public function testSubscriberSkipsSubRequests(): void
    {
        /** @var ChannelContextInterface&MockObject $context */
        $context = $this->createMock(ChannelContextInterface::class);
        $context
            ->expects($this->never())
            ->method('getChannel');

        $subscriber = new LocaleSubscriber($context);
        $request = Request::create('https://example.test/news');

        $subscriber->onKernelRequest($this->createEvent($request, HttpKernelInterface::SUB_REQUEST));

        self::assertSame('en', $request->getLocale());
    }

    public function testSubscriberSupportsLegacyChannelManagerWiring(): void
    {
        $channel = new Channel();
        $channel->setLanguage('nl');

        /** @var ChannelManagerInterface&MockObject $manager */
        $manager = $this->createMock(ChannelManagerInterface::class);
        $manager
            ->expects($this->once())
            ->method('findByDomain')
            ->with('example.test')
            ->willReturn($channel);

        $subscriber = new LocaleSubscriber($manager);
        $request = Request::create('https://example.test/news');

        $subscriber->onKernelRequest($this->createEvent($request, HttpKernelInterface::MAIN_REQUEST));

        self::assertSame('nl', $request->getLocale());
    }

    private function createEvent(Request $request, int $requestType): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $requestType
        );
    }
}
