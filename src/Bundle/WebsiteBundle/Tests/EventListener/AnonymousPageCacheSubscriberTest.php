<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\EventListener;

use Integrated\Bundle\WebsiteBundle\EventListener\AnonymousPageCacheSubscriber;
use Integrated\Bundle\WebsiteBundle\Routing\ContentTypePageLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AnonymousPageCacheSubscriberTest extends TestCase
{
    /** @var AuthorizationCheckerInterface&MockObject */
    private AuthorizationCheckerInterface $authorizationChecker;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
    }

    public function testSubscriberMarksAnonymousContentTypePageResponseAsPublic(): void
    {
        $this->authorizationChecker
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(false);

        $subscriber = new AnonymousPageCacheSubscriber($this->authorizationChecker, 600);

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
        $this->authorizationChecker
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(true);

        $subscriber = new AnonymousPageCacheSubscriber($this->authorizationChecker, 600);

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

        self::assertNull($response->headers->getCacheControlDirective('public'));
        self::assertNull($response->headers->getCacheControlDirective('s-maxage'));
    }

    public function testSubscriberSkipsResponsesThatSetCookies(): void
    {
        $this->authorizationChecker
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(false);

        $subscriber = new AnonymousPageCacheSubscriber($this->authorizationChecker, 600);

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
        $this->authorizationChecker
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_REMEMBERED')
            ->willReturn(false);

        $subscriber = new AnonymousPageCacheSubscriber($this->authorizationChecker, 600);

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
}
