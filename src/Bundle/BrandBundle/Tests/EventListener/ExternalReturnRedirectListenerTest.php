<?php

namespace Integrated\Bundle\BrandBundle\Tests\EventListener;

use Integrated\Bundle\BrandBundle\EventListener\ExternalReturnRedirectListener;
use Integrated\Bundle\ChannelBundle\Event\GetResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\Model\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ExternalReturnRedirectListenerTest extends TestCase
{
    public function testRedirectsAndClearsSessionStateAfterExternalReturn(): void
    {
        $listener = new ExternalReturnRedirectListener();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $config = new Config(42);
        $session->set('externalReturnId', 42);
        $session->set('postReturnUri', '/admin/brand/abc/edit');

        $event = new GetResponseConfigEvent($config, $request);
        $listener->handleExternalReturn($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/admin/brand/abc/edit', $response->getTargetUrl());
        self::assertFalse($session->has('externalReturnId'));
        self::assertFalse($session->has('postReturnUri'));
    }
}
