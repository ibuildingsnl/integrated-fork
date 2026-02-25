<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepository;
use Integrated\Bundle\ContentBundle\Provider\MediaProvider;
use Integrated\Bundle\ContentBundle\Services\CalendarOptions;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Locks\Request as LockRequest;
use Integrated\Common\Locks\Resource;
use Integrated\Common\Locks\Provider\DBAL\Lock as DbalLock;
use Integrated\Common\Locks\Provider\DBAL\Manager;
use Integrated\Common\Queue\Provider\DBAL\QueueProvider;
use Integrated\Common\Security\Permissions;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Translation\TranslatorInterface;
use Integrated\Common\ContentType\ResolverInterface;

class ContentLockControllerTest extends TestCase
{
    public function testLockReturnsForbiddenWhenEditPermissionIsDenied(): void
    {
        $content = (new Article())->setId('content-id');
        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'content-id' ? $content : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::never())->method('findByResource');

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager());
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, false);

        $response = $controller->lock(new Request([], ['_token' => 'valid']), 'content-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertFalse($payload['acquired']);
        self::assertNull($payload['lock']);
        self::assertSame('You are not allowed to lock this document.', $payload['message']);
    }

    public function testLockReturnsForbiddenWhenCsrfTokenIsInvalid(): void
    {
        $content = (new Article())->setId('content-id');
        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'content-id' ? $content : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::never())->method('findByResource');

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager());
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);
        $controller->setCsrfValid(false);

        $response = $controller->lock(new Request([], ['_token' => 'invalid']), 'content-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        self::assertFalse($payload['acquired']);
        self::assertNull($payload['lock']);
        self::assertSame('The lock request token is invalid. Please reload and try again.', $payload['message']);
    }

    public function testLockReturnsLockedWhenSameUserHasAnotherTabLockWithoutKnownLock(): void
    {
        $content = (new Article())->setId('content-id');
        $owner = $this->createUser('alice@example.test');
        $existingLock = $this->createLock($content, $owner, 'lock-1');

        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'content-id' ? $content : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::once())->method('findByResource')->willReturn([$existingLock]);

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager(), $owner);
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);

        $response = $controller->lock(new Request([], ['_token' => 'valid']), 'content-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_LOCKED, $response->getStatusCode());
        self::assertFalse($payload['acquired']);
        self::assertNull($payload['lock']);
        self::assertStringContainsString('currently locked by your self', $payload['message']);
    }

    public function testLockReturnsAcquiredWhenKnownLockMatchesSameOwnerLock(): void
    {
        $content = (new Article())->setId('content-id');
        $owner = $this->createUser('alice@example.test');
        $existingLock = $this->createLock($content, $owner, 'lock-1');

        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'content-id' ? $content : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::once())->method('findByResource')->willReturn([$existingLock]);

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager(), $owner);
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);

        $response = $controller->lock(new Request([], ['_token' => 'valid', 'known_lock' => 'lock-1']), 'content-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($payload['acquired']);
        self::assertSame('lock-1', $payload['lock']);
    }

    public function testLockReleasesDuplicateOwnerLocksAndKeepsSingleCanonicalLock(): void
    {
        $content = (new Article())->setId('content-id');
        $owner = $this->createUser('alice@example.test');
        $firstOwnerLock = $this->createLock($content, $owner, 'lock-1');
        $duplicateOwnerLock = $this->createLock($content, $owner, 'lock-2');

        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'content-id' ? $content : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::once())->method('findByResource')->willReturn([$firstOwnerLock, $duplicateOwnerLock]);
        $lockManager->expects(self::once())
            ->method('release')
            ->with(self::callback(static fn (mixed $lock): bool => $lock instanceof DbalLock && $lock->getId() === 'lock-2'));

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager(), $owner);
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);

        $response = $controller->lock(new Request([], ['_token' => 'valid', 'known_lock' => 'lock-1']), 'content-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($payload['acquired']);
        self::assertSame('lock-1', $payload['lock']);
    }

    public function testLockReturnsLockedWithOwnerNameWhenLockedByAnotherUser(): void
    {
        $content = (new Article())->setId('content-id');
        $currentUser = $this->createUser('alice@example.test');
        $otherUser = $this->createUser('bob@example.test');
        $existingLock = $this->createLock($content, $otherUser, 'lock-2');

        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'content-id' ? $content : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::once())->method('findByResource')->willReturn([$existingLock]);

        $userManager = $this->createUserManager();
        $userManager->expects(self::once())->method('findByUsername')->with('bob@example.test')->willReturn($otherUser);

        $controller = $this->createController($documentManager, $lockManager, $userManager, $currentUser);
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);

        $response = $controller->lock(new Request([], ['_token' => 'valid']), 'content-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_LOCKED, $response->getStatusCode());
        self::assertFalse($payload['acquired']);
        self::assertSame('bob@example.test', $payload['user']);
        self::assertStringContainsString('bob@example.test', $payload['message']);
    }

    public function testLockAcquiresNewLockWhenNoExistingLockExists(): void
    {
        $content = (new Article())->setId('content-id');
        $owner = $this->createUser('alice@example.test');
        $newLock = $this->createLock($content, $owner, 'lock-new');

        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'content-id' ? $content : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::once())->method('findByResource')->willReturn([]);
        $lockManager->expects(self::once())->method('acquire')->willReturn($newLock);

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager(), $owner);
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);

        $response = $controller->lock(new Request([], ['_token' => 'valid']), 'content-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($payload['acquired']);
        self::assertSame('lock-new', $payload['lock']);
    }

    public function testLockBypassesLockManagerForFileContent(): void
    {
        $file = (new File())->setId('file-id');
        $documentManager = $this->createDocumentManager(static fn (string $id): ?Content => $id === 'file-id' ? $file : null);
        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::never())->method('findByResource');

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager(), $this->createUser('alice@example.test'));
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);

        $response = $controller->lock(new Request([], ['_token' => 'valid']), 'file-id');
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($payload['acquired']);
        self::assertNull($payload['lock']);
    }

    public function testLockResolvesSlugLikeIdentifierToContentIdFallback(): void
    {
        $id = '4054e2aaf1183d4fb94d5939cf17e67e';
        $requestId = 'advertorial-'.$id;
        $file = (new File())->setId($id);

        $repositoryCalls = [];
        $documentManager = $this->createDocumentManager(function (string $lookup) use ($requestId, $id, $file, &$repositoryCalls): ?Content {
            $repositoryCalls[] = $lookup;

            if ($lookup === $requestId) {
                return null;
            }

            if ($lookup === $id) {
                return $file;
            }

            return null;
        });

        $lockManager = $this->createMock(Manager::class);
        $lockManager->expects(self::never())->method('acquire');
        $lockManager->expects(self::never())->method('findByResource');

        $controller = $this->createController($documentManager, $lockManager, $this->createUserManager(), $this->createUser('alice@example.test'));
        $controller->setPermission(Permissions::VIEW, true);
        $controller->setPermission(Permissions::EDIT, true);

        $response = $controller->lock(new Request([], ['_token' => 'valid']), $requestId);
        $payload = $this->decodeResponse($response);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertTrue($payload['acquired']);
        self::assertNull($payload['lock']);
        self::assertSame([$requestId, $id], $repositoryCalls);
    }

    private function decodeResponse(Response $response): array
    {
        $decoded = json_decode((string) $response->getContent(), true);
        self::assertIsArray($decoded);

        return $decoded;
    }

    private function createDocumentManager(callable $finder): DocumentManager
    {
        $repository = $this->createMock(DocumentRepository::class);
        $repository->method('find')->willReturnCallback($finder);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->method('getRepository')->with(Content::class)->willReturn($repository);

        return $documentManager;
    }

    private function createUserManager(): UserManagerInterface&MockObject
    {
        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('getClassName')->willReturn(User::class);

        return $userManager;
    }

    private function createController(
        DocumentManager $documentManager,
        Manager $lockManager,
        UserManagerInterface $userManager,
        ?User $currentUser = null
    ): TestableContentController {
        $controller = new TestableContentController(
            $this->createStub(ResolverInterface::class),
            $this->createStub(ContentTypeManager::class),
            $this->createStub(QueueSubscriber::class),
            $this->createStub(LockFactory::class),
            $this->createStub(IndexerInterface::class),
            $this->createStub(SearchContentReferenced::class),
            $lockManager,
            $userManager,
            $this->createStub(ImageExtension::class),
            $this->createStub(MediaProvider::class),
            $this->createStub(TaxonomyOverview::class),
            $this->createStub(QueryFactoryInterface::class),
            $this->createStub(MetadataFactoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
            $documentManager,
            $this->createStub(CalendarOptions::class),
            $this->createStub(QueueProvider::class),
            $this->createStub(PublicationRepository::class),
        );

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => $id);

        $controller->setTranslatorStub($translator);
        $controller->setCurrentUser($currentUser);

        return $controller;
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->setUsername($username);

        return $user;
    }

    private function createLock(Content $content, User $owner, string $id): DbalLock
    {
        $request = new LockRequest(Resource::fromObject($content));
        $request->setOwner(Resource::fromAccount($owner));
        $request->setTimeout(15);

        return new DbalLock(
            $id,
            $request,
            new \DateTime('-1 minute'),
            new \DateTime('+5 minutes')
        );
    }
}

class TestableContentController extends ContentController
{
    private bool $csrfValid = true;
    private array $permissions = [];
    private ?User $currentUser = null;
    private TranslatorInterface $translator;

    public function setCsrfValid(bool $csrfValid): void
    {
        $this->csrfValid = $csrfValid;
    }

    public function setPermission(string $permission, bool $granted): void
    {
        $this->permissions[$permission] = $granted;
    }

    public function setCurrentUser(?User $user): void
    {
        $this->currentUser = $user;
    }

    public function setTranslatorStub(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        if (\array_key_exists((string) $attribute, $this->permissions)) {
            return $this->permissions[(string) $attribute];
        }

        return true;
    }

    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfValid;
    }

    protected function getUser(): ?User
    {
        return $this->currentUser;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
