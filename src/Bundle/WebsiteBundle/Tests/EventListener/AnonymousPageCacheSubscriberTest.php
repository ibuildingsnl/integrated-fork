<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\EventListener;

use Integrated\Bundle\WebsiteBundle\EventListener\AnonymousPageCacheSubscriber;
use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class AnonymousPageCacheSubscriberTest extends TestCase
{
    public function testSubscriberMarksAnonymousContentTypePageResponseAsPublic(): void
    {
        $subscriber = new AnonymousPageCacheSubscriber(600);

        $request = Request::create('https://example.test/articles/test');
        $request->attributes->set('_route', ContentTypePageLoader::ROUTE_PREFIX.'_abc123');
        $response = new Response('<html><body>ok</body></html>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $subscriber->onKernelResponse($event);

        self::assertFalse((bool) $response->headers->getCacheControlDirective('private'));
        self::assertTrue((bool) $response->headers->getCacheControlDirective('public'));
        self::assertSame(600, (int) $response->headers->getCacheControlDirective('max-age'));
        self::assertSame(600, (int) $response->headers->getCacheControlDirective('s-maxage'));
    }

    public function testSubscriberSkipsAuthenticatedRequest(): void
    {
        $subscriber = new AnonymousPageCacheSubscriber(600);

        $request = Request::create('https://example.test/articles/test');
        $request->attributes->set('_route', ContentTypePageLoader::ROUTE_PREFIX.'_abc123');
        $request->cookies->set(session_name(), 'sess-123');
        $response = new Response('<html><body>ok</body></html>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $subscriber->onKernelResponse($event);

        self::assertNull($response->headers->getCacheControlDirective('public'));
        self::assertNull($response->headers->getCacheControlDirective('s-maxage'));
    }

    public function testSubscriberSkipsResponsesThatSetCookies(): void
    {
        $subscriber = new AnonymousPageCacheSubscriber(600);

        $request = Request::create('https://example.test/articles/test');
        $request->attributes->set('_route', ContentTypePageLoader::ROUTE_PREFIX.'_abc123');
        $response = new Response('<html><body>ok</body></html>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
        $response->headers->setCookie(new Cookie('foo', 'bar'));

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $subscriber->onKernelResponse($event);

        self::assertNull($response->headers->getCacheControlDirective('public'));
        self::assertNull($response->headers->getCacheControlDirective('s-maxage'));
    }

    public function testSubscriberSkipsNoindexResponses(): void
    {
        $subscriber = new AnonymousPageCacheSubscriber(600);

        $request = Request::create('https://example.test/articles/test');
        $request->attributes->set('_route', ContentTypePageLoader::ROUTE_PREFIX.'_abc123');
        $response = new Response('<html><body>ok</body></html>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $subscriber->onKernelResponse($event);

        self::assertNull($response->headers->getCacheControlDirective('public'));
        self::assertNull($response->headers->getCacheControlDirective('s-maxage'));
    }

    public function testSubscriberSkipsRememberMeCookieRequests(): void
    {
        $subscriber = new AnonymousPageCacheSubscriber(600);

        $request = Request::create('https://example.test/articles/test');
        $request->attributes->set('_route', ContentTypePageLoader::ROUTE_PREFIX.'_abc123');
        $request->cookies->set('REMEMBERME', 'remember-token');
        $response = new Response('<html><body>ok</body></html>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);

        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $subscriber->onKernelResponse($event);

        self::assertNull($response->headers->getCacheControlDirective('public'));
        self::assertNull($response->headers->getCacheControlDirective('s-maxage'));
    }
}
