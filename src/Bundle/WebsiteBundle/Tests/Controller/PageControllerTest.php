<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Controller\PageController;
use Integrated\Bundle\WebsiteBundle\EventListener\WebsiteToolbarListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageControllerTest extends TestCase
{
    /** @var ThemeManager&MockObject */
    private ThemeManager $themeManager;
    /** @var WebsiteToolbarListener&MockObject */
    private WebsiteToolbarListener $websiteToolbarListener;
    private UriSigner $uriSigner;

    protected function setUp(): void
    {
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->websiteToolbarListener = $this->createMock(WebsiteToolbarListener::class);
        $this->uriSigner = new UriSigner('website-preview-test-secret');
    }

    public function testShowThrowsNotFoundForDisabledPageWithoutAdminRole(): void
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(true);
        $page->setPath('/draft-page');

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show(Request::create('https://example.test/draft-page'), $page);
    }

    public function testShowAllowsDisabledPageForAdmin(): void
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(true);
        $page->setPath('/draft-page');

        $this->themeManager
            ->expects($this->once())
            ->method('locateTemplate')
            ->with('default.html.twig')
            ->willReturn('layout.html.twig');
        $this->websiteToolbarListener
            ->expects($this->once())
            ->method('setToolbarMessage')
            ->with('This item is currently unpublished');

        $controller = $this->createController([
            'ROLE_ADMIN' => true,
        ]);

        $response = $controller->show(Request::create('https://example.test/draft-page'), $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue((bool) $response->headers->getCacheControlDirective('private'));
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
        self::assertSame(0, (int) $response->headers->getCacheControlDirective('max-age'));
        self::assertStringNotContainsString('integrated-draft-notice', (string) $response->getContent());
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testShowAllowsDisabledPageWithValidPreviewLinkWithoutAdminRole(): void
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(true);
        $page->setPath('/draft-page');

        $this->themeManager
            ->expects($this->once())
            ->method('locateTemplate')
            ->with('default.html.twig')
            ->willReturn('layout.html.twig');
        $this->websiteToolbarListener
            ->expects($this->once())
            ->method('setToolbarMessage')
            ->with('This item is currently unpublished');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $request = $this->createSignedPreviewRequest('/draft-page', time() + 600);
        $response = $controller->show($request, $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
        self::assertStringNotContainsString('integrated-draft-notice', (string) $response->getContent());
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testShowThrowsNotFoundForFuturePublishPageWithoutPreviewAccess(): void
    {
        $page = $this->createLifecyclePage();
        $page->setPublishAt(new \DateTimeImmutable('+1 day'));

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show(Request::create('https://example.test/scheduled-page'), $page);
    }

    public function testShowAllowsFuturePublishPageWithValidPreviewLink(): void
    {
        $page = $this->createLifecyclePage();
        $page->setPublishAt(new \DateTimeImmutable('+1 day'));

        $this->themeManager
            ->expects($this->once())
            ->method('locateTemplate')
            ->with('default.html.twig')
            ->willReturn('layout.html.twig');
        $this->websiteToolbarListener
            ->expects($this->once())
            ->method('setToolbarMessage')
            ->with('This item is currently unpublished');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $response = $controller->show($this->createSignedPreviewRequest('/scheduled-page', time() + 600), $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue((bool) $response->headers->getCacheControlDirective('private'));
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
        self::assertSame(0, (int) $response->headers->getCacheControlDirective('max-age'));
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testShowAllowsFuturePublishPageForAdminPreview(): void
    {
        $page = $this->createLifecyclePage();
        $page->setPublishAt(new \DateTimeImmutable('+1 day'));

        $this->themeManager
            ->expects($this->once())
            ->method('locateTemplate')
            ->with('default.html.twig')
            ->willReturn('layout.html.twig');
        $this->websiteToolbarListener
            ->expects($this->once())
            ->method('setToolbarMessage')
            ->with('This item is currently unpublished');

        $controller = $this->createController([
            'ROLE_ADMIN' => true,
        ]);

        $response = $controller->show(Request::create('https://example.test/scheduled-page'), $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue((bool) $response->headers->getCacheControlDirective('private'));
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
        self::assertSame(0, (int) $response->headers->getCacheControlDirective('max-age'));
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testShowThrowsNotFoundForExpiredPageWithoutRedirectWithoutPreviewAccess(): void
    {
        $page = $this->createLifecyclePage();
        $page->setExpireAt(new \DateTimeImmutable('-1 day'));

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show(Request::create('https://example.test/expired-page'), $page);
    }

    public function testShowRedirectsExpiredPageWithoutPreviewAccess(): void
    {
        $page = $this->createLifecyclePage();
        $page->setExpireAt(new \DateTimeImmutable('-1 day'));
        $page->setExpireRedirectUrl('/archive/expired-page');

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $response = $controller->show(Request::create('https://example.test/expired-page'), $page);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame('/archive/expired-page', $response->headers->get('Location'));
    }

    public function testShowDoesNotRedirectExpiredPageToUnsupportedScheme(): void
    {
        $page = $this->createLifecyclePage();
        $page->setExpireAt(new \DateTimeImmutable('-1 day'));
        $page->setExpireRedirectUrl('mailto:test@example.com');

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show(Request::create('https://example.test/expired-page'), $page);
    }

    public function testShowDoesNotRedirectExpiredPageToProtocolRelativeUrl(): void
    {
        $page = $this->createLifecyclePage();
        $page->setExpireAt(new \DateTimeImmutable('-1 day'));
        $page->setExpireRedirectUrl('//evil.test/archive');

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show(Request::create('https://example.test/expired-page'), $page);
    }

    public function testShowDoesNotRedirectExpiredPageToAbsoluteUrlWithUserInfo(): void
    {
        $page = $this->createLifecyclePage();
        $page->setExpireAt(new \DateTimeImmutable('-1 day'));
        $page->setExpireRedirectUrl('https://user@evil.test/archive');

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show(Request::create('https://example.test/expired-page'), $page);
    }

    public function testShowAllowsExpiredPageForAdminPreview(): void
    {
        $page = $this->createLifecyclePage();
        $page->setExpireAt(new \DateTimeImmutable('-1 day'));
        $page->setExpireRedirectUrl('/archive/expired-page');

        $this->themeManager
            ->expects($this->once())
            ->method('locateTemplate')
            ->with('default.html.twig')
            ->willReturn('layout.html.twig');
        $this->websiteToolbarListener
            ->expects($this->once())
            ->method('setToolbarMessage')
            ->with('This item is currently unpublished');

        $controller = $this->createController([
            'ROLE_ADMIN' => true,
        ]);

        $response = $controller->show(Request::create('https://example.test/expired-page'), $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue((bool) $response->headers->getCacheControlDirective('private'));
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
        self::assertSame(0, (int) $response->headers->getCacheControlDirective('max-age'));
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testShowAllowsExpiredPageWithSignedPreviewLink(): void
    {
        $page = $this->createLifecyclePage();
        $page->setExpireAt(new \DateTimeImmutable('-1 day'));
        $page->setExpireRedirectUrl('/archive/expired-page');

        $this->themeManager
            ->expects($this->once())
            ->method('locateTemplate')
            ->with('default.html.twig')
            ->willReturn('layout.html.twig');
        $this->websiteToolbarListener
            ->expects($this->once())
            ->method('setToolbarMessage')
            ->with('This item is currently unpublished');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $response = $controller->show($this->createSignedPreviewRequest('/scheduled-page', time() + 600), $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue((bool) $response->headers->getCacheControlDirective('private'));
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
        self::assertSame(0, (int) $response->headers->getCacheControlDirective('max-age'));
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testShowRejectsDisabledPageWithValidSignatureOnWrongHost(): void
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(true);
        $page->setPath('/draft-page');
        $channel = new Channel();
        $channel->setPrimaryDomain('example.test');
        $page->setChannel($channel);

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show($this->createSignedPreviewRequest('/draft-page', time() + 600, 'other.test'), $page);
    }

    public function testShowRejectsDisabledPageWithExpiredPreviewLinkWithoutAdminRole(): void
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(true);
        $page->setPath('/draft-page');

        $this->themeManager->expects($this->never())->method('locateTemplate');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $this->expectException(NotFoundHttpException::class);
        $controller->show($this->createSignedPreviewRequest('/draft-page', time() - 10), $page);
    }

    public function testShowAllowsPublishedPageWithoutAdminRole(): void
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(false);
        $page->setPath('/published-page');

        $this->themeManager
            ->expects($this->once())
            ->method('locateTemplate')
            ->with('default.html.twig')
            ->willReturn('layout.html.twig');
        $this->websiteToolbarListener->expects($this->never())->method('setToolbarMessage');

        $controller = $this->createController([
            'ROLE_WEBSITE_MANAGER' => false,
            'ROLE_ADMIN' => false,
        ]);

        $response = $controller->show(Request::create('https://example.test/published-page'), $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertFalse($response->headers->hasCacheControlDirective('no-store'));
        self::assertStringNotContainsString('integrated-draft-notice', (string) $response->getContent());
    }

    /**
     * @param array<string, bool> $grants
     */
    private function createController(array $grants): PageController
    {
        $themeManager = $this->themeManager;
        $websiteToolbarListener = $this->websiteToolbarListener;
        $uriSigner = $this->uriSigner;

        return new class($themeManager, $websiteToolbarListener, $uriSigner, $grants) extends PageController {
            /**
             * @param array<string, bool> $grants
             */
            public function __construct(ThemeManager $themeManager, WebsiteToolbarListener $websiteToolbarListener, UriSigner $uriSigner, private readonly array $grants)
            {
                parent::__construct($themeManager, $websiteToolbarListener, $uriSigner);
            }

            protected function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return (bool) ($this->grants[(string) $attribute] ?? false);
            }

            /**
             * @param array<string, mixed> $parameters
             */
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                return $response ?? new Response('<html><body>ok</body></html>');
            }
        };
    }

    private function createLifecyclePage(): Page
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(false);
        $page->setPath('/scheduled-page');

        return $page;
    }

    private function createSignedPreviewRequest(string $path, int $expires, string $host = 'example.test'): Request
    {
        $unsigned = \sprintf('https://%s%s?preview_expires=%d', $host, $path, $expires);
        $signed = $this->uriSigner->sign($unsigned);

        return Request::create($signed);
    }
}
