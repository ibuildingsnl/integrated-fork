<?php

namespace Integrated\Bundle\BrandBundle\Tests\EventListener;

use Integrated\Bundle\BrandBundle\EventListener\ConnectorDeletionRedirectListener;
use Integrated\Bundle\ChannelBundle\Event\FilterResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\Model\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ConnectorDeletionRedirectListenerTest extends TestCase
{
    public function testRedirectsWithAdapterAndConfigIdScopedSessionKey(): void
    {
        $listener = new ConnectorDeletionRedirectListener();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $config = (new Config(24))->setAdapter('office365');
        $key = \sprintf(ConnectorDeletionRedirectListener::SESSION_PATH, 'office365', 24);
        $session->set($key, '/admin/brand/abc/edit');

        $event = new FilterResponseConfigEvent($config, $request, new Response('original'));
        $listener->redirect($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/admin/brand/abc/edit', $response->getTargetUrl());
        self::assertFalse($session->has($key));
    }

    public function testDoesNotRedirectWhenOnlyDifferentConfigIdKeyExists(): void
    {
        $listener = new ConnectorDeletionRedirectListener();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $config = (new Config(24))->setAdapter('office365');
        $otherKey = \sprintf(ConnectorDeletionRedirectListener::SESSION_PATH, 'office365', 99);
        $session->set($otherKey, '/admin/brand/other/edit');

        $event = new FilterResponseConfigEvent($config, $request, new Response('original'));
        $listener->redirect($event);

        self::assertSame('original', $event->getResponse()->getContent());
        self::assertTrue($session->has($otherKey));
    }
}
