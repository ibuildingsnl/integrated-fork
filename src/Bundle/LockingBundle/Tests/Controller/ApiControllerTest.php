<?php

declare(strict_types=1);

namespace Integrated\Bundle\LockingBundle\Tests\Controller;

use Integrated\Bundle\LockingBundle\Controller\ApiController;
use Integrated\Common\Locks\LockInterface;
use Integrated\Common\Locks\ManagerInterface;
use Integrated\Common\Locks\RequestInterface;
use Integrated\Common\Locks\Resource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiControllerTest extends TestCase
{
    public function testRefreshRequiresManager(): void
    {
        $controller = new TestApiController(null);
        $controller->testUser = new TestUser('editor');

        $response = $controller->refresh($this->createRequest('lock-1'));

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testRefreshRequiresUser(): void
    {
        $controller = new TestApiController($this->createMock(ManagerInterface::class));

        $response = $controller->refresh($this->createRequest('lock-1'));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testRefreshReturnsLockedForDifferentOwner(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $controller = new TestApiController($manager);
        $controller->testUser = new TestUser('editor-a');

        $manager->expects(self::once())
            ->method('find')
            ->with('lock-1')
            ->willReturn($this->createLock('lock-1', new Resource(TestUser::class, 'editor-b')));
        $manager->expects(self::never())->method('refresh');

        $response = $controller->refresh($this->createRequest('lock-1'));

        self::assertSame(Response::HTTP_LOCKED, $response->getStatusCode());
    }

    public function testRefreshExtendsLockForOwner(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $controller = new TestApiController($manager);
        $controller->testUser = new TestUser('editor-a');
        $ownerResource = Resource::fromAccount($controller->testUser);

        $manager->expects(self::once())
            ->method('find')
            ->with('lock-1')
            ->willReturn($this->createLock('lock-1', $ownerResource));
        $manager->expects(self::once())
            ->method('refresh')
            ->willReturn($this->createLock('lock-1', $ownerResource));

        $response = $controller->refresh($this->createRequest('lock-1'));
        $payload = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('lock-1', $payload['lock']);
    }

    public function testReleaseReleasesLockForOwner(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $controller = new TestApiController($manager);
        $controller->testUser = new TestUser('editor-a');
        $ownerResource = Resource::fromAccount($controller->testUser);
        $lock = $this->createLock('lock-1', $ownerResource);

        $manager->expects(self::once())
            ->method('find')
            ->with('lock-1')
            ->willReturn($lock);
        $manager->expects(self::once())
            ->method('release')
            ->with($lock);

        $response = $controller->release($this->createRequest('lock-1'));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    private function createRequest(string $lockId): Request
    {
        return new Request([], ['lock' => $lockId], [], [], [], ['REQUEST_METHOD' => 'POST']);
    }

    private function createLock(string $lockId, Resource $owner): LockInterface
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getOwner')->willReturn($owner);

        $lock = $this->createMock(LockInterface::class);
        $lock->method('getId')->willReturn($lockId);
        $lock->method('getRequest')->willReturn($request);

        return $lock;
    }
}

final class TestApiController extends ApiController
{
    public ?UserInterface $testUser = null;

    protected function getUser(): ?UserInterface
    {
        return $this->testUser;
    }
}

final class TestUser implements UserInterface
{
    /** @var non-empty-string */
    private readonly string $identifier;

    /**
     * @param non-empty-string $identifier
     */
    public function __construct(
        string $identifier,
    ) {
        $this->identifier = $identifier;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }
}
