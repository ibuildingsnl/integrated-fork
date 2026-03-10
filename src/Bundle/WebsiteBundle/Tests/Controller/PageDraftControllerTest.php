<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\PageEditDraft;
use Integrated\Bundle\PageBundle\Document\Page\PageEditDraftRepository;
use Integrated\Bundle\PageBundle\Grid\GridFactory;
use Integrated\Bundle\WebsiteBundle\Controller\PageDraftController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Security\Core\User\UserInterface;

final class PageDraftControllerTest extends TestCase
{
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    /** @var ObjectRepository&MockObject */
    private ObjectRepository $pageRepository;
    /** @var PageEditDraftRepository&MockObject */
    private PageEditDraftRepository $draftRepository;
    /** @var GridFactory&MockObject */
    private GridFactory $gridFactory;
    private UriSigner $uriSigner;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->pageRepository = $this->createMock(ObjectRepository::class);
        $this->draftRepository = $this->createMock(PageEditDraftRepository::class);
        $this->gridFactory = $this->createMock(GridFactory::class);
        $this->uriSigner = new UriSigner('page-draft-controller-test-secret');

        $this->documentManager
            ->method('getRepository')
            ->willReturnMap([
                [AbstractPage::class, $this->pageRepository],
                [PageEditDraft::class, $this->draftRepository],
            ]);
    }

    public function testGetDraftReturnsExistsFalseWhenNoDraftIsStored(): void
    {
        $page = new Page();
        $page->setPath('/example');
        $this->pageRepository->method('find')->with('page-1')->willReturn($page);
        $this->draftRepository->method('findOneByPageAndUser')->with('page-1', 'user-1')->willReturn(null);

        $controller = $this->createController();
        $response = $controller->getDraft(Request::create('/page-draft/page-1', 'GET'), 'page-1');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(['exists' => false], json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR));
    }

    public function testSaveDraftCreatesNewDraftDocument(): void
    {
        $page = new Page();
        $page->setPath('/example');
        $this->pageRepository->method('find')->with('page-1')->willReturn($page);
        $this->draftRepository->method('findOneByPageAndUser')->with('page-1', 'user-1')->willReturn(null);

        $this->documentManager->expects($this->once())->method('persist')->with($this->isInstanceOf(PageEditDraft::class));
        $this->documentManager->expects($this->once())->method('flush');

        $controller = $this->createController();
        $response = $controller->saveDraft(
            Request::create(
                '/page-draft/page-1',
                'POST',
                [],
                [],
                [],
                [],
                (string) json_encode([
                    'gridPayload' => ['grid' => [['block' => 'text']]],
                    'menuPayload' => ['menu' => [['name' => 'Main']]],
                    'basePageUpdatedAt' => '2026-03-10T09:00:00+00:00',
                ])
            ),
            'page-1'
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $json = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertTrue((bool) ($json['saved'] ?? false));
        self::assertArrayHasKey('updatedAt', $json);
    }

    public function testDeleteDraftRemovesExistingDraft(): void
    {
        $page = new Page();
        $page->setPath('/example');
        $draft = new PageEditDraft('page-1', 'user-1');
        $this->pageRepository->method('find')->with('page-1')->willReturn($page);
        $this->draftRepository->method('findOneByPageAndUser')->with('page-1', 'user-1')->willReturn($draft);

        $this->documentManager->expects($this->once())->method('remove')->with($draft);
        $this->documentManager->expects($this->once())->method('flush');

        $controller = $this->createController();
        $response = $controller->deleteDraft(Request::create('/page-draft/page-1', 'DELETE'), 'page-1');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(['removed' => true], json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR));
    }

    public function testPublishDraftAppliesDraftGridPayloadAndClearsDraft(): void
    {
        $page = new Page();
        $page->setPath('/example');
        $page->setLayout('default.html.twig');
        $page->setUpdatedAt(new \DateTime('2026-03-10T08:30:00+00:00'));

        $draft = new PageEditDraft('page-1', 'user-1');
        $draft->setGridPayload([
            ['id' => 'main', 'items' => []],
        ]);
        $draft->setBasePageUpdatedAt('2026-03-10T09:00:00+00:00');

        $this->pageRepository->method('find')->with('page-1')->willReturn($page);
        $this->draftRepository->method('findOneByPageAndUser')->with('page-1', 'user-1')->willReturn($draft);
        $this->gridFactory->expects($this->once())->method('fromArray')->willReturn(new Grid('main'));

        $this->documentManager->expects($this->once())->method('remove')->with($draft);
        $this->documentManager->expects($this->once())->method('flush');

        $controller = $this->createController();
        $response = $controller->publishDraft(Request::create('/page-draft/page-1/publish', 'POST'), 'page-1');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue((bool) json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR)['published']);
        self::assertCount(1, $page->getGrids());
    }

    public function testPublishDraftReturnsConflictWhenPageChangedAfterBaseline(): void
    {
        $page = new Page();
        $page->setPath('/example');
        $page->setLayout('default.html.twig');
        $page->setUpdatedAt(new \DateTime('2026-03-10T11:00:00+00:00'));

        $draft = new PageEditDraft('page-1', 'user-1');
        $draft->setGridPayload([
            ['id' => 'main', 'items' => []],
        ]);
        $draft->setBasePageUpdatedAt('2026-03-10T09:00:00+00:00');

        $this->pageRepository->method('find')->with('page-1')->willReturn($page);
        $this->draftRepository->method('findOneByPageAndUser')->with('page-1', 'user-1')->willReturn($draft);

        $this->documentManager->expects($this->never())->method('remove');
        $this->documentManager->expects($this->never())->method('flush');

        $controller = $this->createController();
        $response = $controller->publishDraft(Request::create('/page-draft/page-1/publish', 'POST'), 'page-1');

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertTrue((bool) json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR)['conflict']);
    }

    public function testCreatePreviewLinkReturnsSignedUrl(): void
    {
        $page = new Page();
        $page->setPath('/draft-page');
        $page->setLayout('default.html.twig');
        $draft = new PageEditDraft('page-1', 'user-1');
        $draft->setId('draft-123');
        $this->pageRepository->method('find')->with('page-1')->willReturn($page);
        $this->draftRepository->method('findOneByPageAndUser')->with('page-1', 'user-1')->willReturn($draft);

        $controller = $this->createController();
        $response = $controller->createPreviewLink(
            Request::create('https://example.test/page-draft/page-1/preview-link', 'POST'),
            'page-1'
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $json = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertTrue((bool) ($json['created'] ?? false));
        self::assertStringContainsString('preview_expires=', (string) ($json['url'] ?? ''));
        self::assertStringContainsString('page_draft_preview=draft-123', (string) ($json['url'] ?? ''));
        self::assertStringContainsString('_hash=', (string) ($json['url'] ?? ''));
    }

    private function createController(): PageDraftController
    {
        $documentManager = $this->documentManager;
        $gridFactory = $this->gridFactory;
        $uriSigner = $this->uriSigner;
        $grants = [
            'ROLE_WEBSITE_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ];
        $user = new class implements UserInterface {
            public function getUserIdentifier(): string
            {
                return 'user-1';
            }

            public function getRoles(): array
            {
                return ['ROLE_WEBSITE_MANAGER'];
            }

            public function eraseCredentials(): void
            {
            }
        };

        return new class($documentManager, $gridFactory, $uriSigner, $grants, $user) extends PageDraftController {
            /**
             * @param array<string, bool> $grants
             */
            public function __construct(DocumentManager $documentManager, GridFactory $gridFactory, UriSigner $uriSigner, private readonly array $grants, private readonly UserInterface $user)
            {
                parent::__construct($documentManager, $gridFactory, $uriSigner);
            }

            protected function isGranted(mixed $attribute, mixed $subject = null): bool
            {
                return (bool) ($this->grants[(string) $attribute] ?? false);
            }

            public function getUser(): ?UserInterface
            {
                return $this->user;
            }
        };
    }
}
