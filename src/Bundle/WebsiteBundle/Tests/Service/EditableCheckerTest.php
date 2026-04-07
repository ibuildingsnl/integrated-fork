<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\WebsiteBundle\Routing\PageLoader;
use Integrated\Bundle\WebsiteBundle\Service\EditableChecker;
use Integrated\Common\Security\PermissionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EditableCheckerTest extends TestCase
{
    /** @var AuthorizationCheckerInterface&MockObject */
    private AuthorizationCheckerInterface $authorizationChecker;
    /** @var TokenStorageInterface&MockObject */
    private TokenStorageInterface $tokenStorage;
    /** @var RouterInterface&MockObject */
    private RouterInterface $router;
    /** @var DocumentManager&MockObject */
    private DocumentManager $documentManager;
    /** @var DocumentRepository<AbstractPage>&MockObject */
    private DocumentRepository $pageRepository;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->pageRepository = $this->createMock(DocumentRepository::class);
        $this->requestStack = new RequestStack();

        $this->router
            ->method('getRouteCollection')
            ->willReturn(new RouteCollection());
        $this->documentManager
            ->method('getRepository')
            ->with(AbstractPage::class)
            ->willReturn($this->pageRepository);
    }

    public function testCheckEditableReturnsTrueForPageRouteWithChannelWritePermission(): void
    {
        $page = $this->createPage();
        $channel = $page->getChannel();

        $request = $this->createPageRequest();
        $request->cookies->set($this->getSessionCookieName(), 'sess-123');
        $this->requestStack->push($request);
        $this->tokenStorage
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));
        $this->pageRepository
            ->expects($this->once())
            ->method('find')
            ->with('page-id')
            ->willReturn($page);
        $this->authorizationChecker
            ->expects($this->exactly(4))
            ->method('isGranted')
            ->willReturnCallback(static function (mixed $attribute, mixed $subject = null) use ($channel): bool {
                if ($attribute === 'ROLE_SCOPE_INTEGRATED') {
                    return true;
                }

                if ($attribute === PermissionInterface::WRITE && $subject === $channel) {
                    return true;
                }

                return false;
            });

        $checker = $this->createChecker();

        self::assertTrue($checker->checkEditable());
    }

    public function testCheckEditableReturnsFalseForPageRouteWithoutChannelWritePermission(): void
    {
        $page = $this->createPage();
        $channel = $page->getChannel();

        $request = $this->createPageRequest();
        $request->cookies->set($this->getSessionCookieName(), 'sess-123');
        $this->requestStack->push($request);
        $this->tokenStorage
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));
        $this->pageRepository
            ->expects($this->once())
            ->method('find')
            ->with('page-id')
            ->willReturn($page);
        $this->authorizationChecker
            ->expects($this->exactly(4))
            ->method('isGranted')
            ->willReturnCallback(static function (mixed $attribute, mixed $subject = null) use ($channel): bool {
                if ($attribute === 'ROLE_SCOPE_INTEGRATED') {
                    return true;
                }

                if ($attribute === PermissionInterface::WRITE && $subject === $channel) {
                    return false;
                }

                return false;
            });

        $checker = $this->createChecker();

        self::assertFalse($checker->checkEditable());
    }

    public function testCheckEditableReturnsFalseWithoutIntegratedScopeEvenWhenChannelWriteWouldPass(): void
    {
        $page = $this->createPage();
        $channel = $page->getChannel();

        $request = $this->createPageRequest();
        $request->cookies->set($this->getSessionCookieName(), 'sess-123');
        $this->requestStack->push($request);
        $this->tokenStorage
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));
        $this->pageRepository
            ->expects($this->never())
            ->method('find');
        $this->authorizationChecker
            ->expects($this->exactly(3))
            ->method('isGranted')
            ->willReturnCallback(static function (mixed $attribute, mixed $subject = null) use ($channel): bool {
                if ($attribute === PermissionInterface::WRITE && $subject === $channel) {
                    return true;
                }

                return false;
            });

        $checker = $this->createChecker();

        self::assertFalse($checker->checkEditable());
    }

    public function testCheckEditableReturnsFalseWithoutAuthenticationHintCookies(): void
    {
        $this->requestStack->push($this->createPageRequest());
        $this->tokenStorage
            ->expects($this->never())
            ->method('getToken');
        $this->authorizationChecker
            ->expects($this->never())
            ->method('isGranted');
        $this->pageRepository
            ->expects($this->never())
            ->method('find');

        $checker = $this->createChecker();

        self::assertFalse($checker->checkEditable());
    }

    private function createChecker(): EditableChecker
    {
        return new EditableChecker(
            $this->authorizationChecker,
            $this->tokenStorage,
            $this->requestStack,
            $this->router,
            $this->documentManager,
        );
    }

    private function createPageRequest(): Request
    {
        $request = Request::create('/preview');
        $request->attributes->set('_route', PageLoader::ROUTE_PREFIX.'page-id');
        $request->attributes->set('page', 'page-id');

        return $request;
    }

    private function createPage(): Page
    {
        $page = new Page();
        $page->setLayout('default.html.twig');
        $page->setPath('/preview');
        $page->setChannel(new Channel());

        return $page;
    }

    private function getSessionCookieName(): string
    {
        $sessionCookieName = session_name();

        self::assertIsString($sessionCookieName);

        return $sessionCookieName;
    }
}
