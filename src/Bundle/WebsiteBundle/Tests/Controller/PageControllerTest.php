<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\PageEditDraft;
use Integrated\Bundle\PageBundle\Grid\GridFactory;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Controller\PageController;
use Integrated\Bundle\WebsiteBundle\EventListener\WebsiteToolbarListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageControllerTest extends TestCase
{
    /** @var ThemeManager&MockObject */
    private ThemeManager $themeManager;
    /** @var WebsiteToolbarListener&MockObject */
    private WebsiteToolbarListener $websiteToolbarListener;
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    /** @var ObjectRepository&MockObject */
    private ObjectRepository $draftRepository;
    /** @var GridFactory&MockObject */
    private GridFactory $gridFactory;
    private UriSigner $uriSigner;

    protected function setUp(): void
    {
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->websiteToolbarListener = $this->createMock(WebsiteToolbarListener::class);
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->draftRepository = $this->createMock(ObjectRepository::class);
        $this->gridFactory = $this->createMock(GridFactory::class);
        $this->uriSigner = new UriSigner('website-preview-test-secret');
        $this->documentManager
            ->method('getRepository')
            ->with(PageEditDraft::class)
            ->willReturn($this->draftRepository);
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

    public function testShowAppliesDraftGridPreviewWhenSignedDraftPreviewIsProvided(): void
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setDisabled(false);
        $page->setPath('/published-page');

        $draft = new PageEditDraft('', 'user-1');
        $draft->setId('draft-preview-1');
        $draft->setGridPayload([
            ['id' => 'preview-grid', 'items' => []],
        ]);
        $this->draftRepository
            ->expects($this->once())
            ->method('find')
            ->with('draft-preview-1')
            ->willReturn($draft);
        $this->gridFactory
            ->expects($this->once())
            ->method('fromArray')
            ->willReturn(new Grid('preview-grid'));

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

        $request = $this->createSignedDraftPreviewRequest('/published-page', 'draft-preview-1', time() + 600);
        $response = $controller->show($request, $page);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertCount(1, $page->getGrids());
        self::assertSame('preview-grid', $page->getGrids()[0]->getId());
    }

    /**
     * @param array<string, bool> $grants
     */
    private function createController(array $grants): PageController
    {
        $themeManager = $this->themeManager;
        $websiteToolbarListener = $this->websiteToolbarListener;
        $uriSigner = $this->uriSigner;
        $documentManager = $this->documentManager;
        $gridFactory = $this->gridFactory;

        return new class($themeManager, $websiteToolbarListener, $uriSigner, $documentManager, $gridFactory, $grants) extends PageController {
            /**
             * @param array<string, bool> $grants
             */
            public function __construct(ThemeManager $themeManager, WebsiteToolbarListener $websiteToolbarListener, UriSigner $uriSigner, DocumentManager $documentManager, GridFactory $gridFactory, private readonly array $grants)
            {
                parent::__construct($themeManager, $websiteToolbarListener, $uriSigner, $documentManager, $gridFactory);
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

    private function createSignedPreviewRequest(string $path, int $expires, string $host = 'example.test'): Request
    {
        $unsigned = \sprintf('https://%s%s?preview_expires=%d', $host, $path, $expires);
        $signed = $this->uriSigner->sign($unsigned);

        return Request::create($signed);
    }

    private function createSignedDraftPreviewRequest(string $path, string $draftId, int $expires, string $host = 'example.test'): Request
    {
        $unsigned = \sprintf(
            'https://%s%s?preview_expires=%d&page_draft_preview=%s',
            $host,
            $path,
            $expires,
            urlencode($draftId)
        );
        $signed = $this->uriSigner->sign($unsigned);

        return Request::create($signed);
    }
}
