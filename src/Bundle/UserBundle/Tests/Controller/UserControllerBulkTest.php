<?php

namespace Integrated\Bundle\UserBundle\Tests\Controller;

use Integrated\Bundle\UserBundle\Controller\UserController;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Integrated\Bundle\UserBundle\Model\ScopeManagerInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\UserBundle\Service\BulkUserActionService;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserControllerBulkTest extends TestCase
{
    public function testEnableRejectsInvalidCsrfToken(): void
    {
        [$controller, $manager] = $this->createController();
        $controller->csrfValid = false;

        $user = $this->createUser('disabled-user');
        $user->setEnabled(false);

        $manager->method('find')->with('1')->willReturn($user);
        $manager->expects(self::never())->method('persist');

        $request = new Request(['id' => '1'], ['enable_token' => 'invalid'], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $response = $controller->enable($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
    }

    public function testEnablePersistsUserWhenCsrfTokenIsValid(): void
    {
        [$controller, $manager, , , $logger] = $this->createController();
        $controller->currentUser = $this->createUser('admin');

        $user = $this->createUser('disabled-user');
        $user->setEnabled(false);

        $manager->method('find')->with('1')->willReturn($user);
        $manager->expects(self::once())->method('persist')->with($user);

        $logger->expects(self::once())->method('info')->with(
            'User enabled',
            self::callback(static function (array $context): bool {
                return $context['actor'] === 'admin' && $context['target_username'] === 'disabled-user';
            })
        );

        $request = new Request(['id' => '1'], ['enable_token' => 'valid'], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $response = $controller->enable($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('success', $controller->flashes[0]['type']);
        self::assertTrue($user->isEnabled());
    }

    public function testBulkRejectsInvalidCsrfToken(): void
    {
        [$controller] = $this->createController();
        $controller->csrfValid = false;

        $request = new Request([], [
            '_token' => 'invalid',
            'bulk_action' => BulkUserActionService::ACTION_ENABLE_LOGIN,
            'user_ids' => ['1'],
        ]);

        $response = $controller->bulk($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
    }

    public function testBulkRequiresActionAndSelection(): void
    {
        [$controller] = $this->createController();
        $request = new Request([], [
            '_token' => 'valid',
            'bulk_action' => '',
            'user_ids' => [],
        ]);

        $response = $controller->bulk($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('warning', $controller->flashes[0]['type']);
    }

    public function testBulkAssignGroupRequiresGroupSelection(): void
    {
        [$controller, $manager] = $this->createController();
        $user = $this->createUser('u1');
        $manager->method('find')->with('1')->willReturn($user);

        $request = new Request([], [
            '_token' => 'valid',
            'bulk_action' => BulkUserActionService::ACTION_ASSIGN_GROUP,
            'user_ids' => ['1'],
            'bulk_group' => '',
        ]);

        $response = $controller->bulk($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('warning', $controller->flashes[0]['type']);
    }

    public function testBulkChangeScopeRequiresScopeSelection(): void
    {
        [$controller, $manager] = $this->createController();
        $user = $this->createUser('u1');
        $manager->method('find')->with('1')->willReturn($user);

        $request = new Request([], [
            '_token' => 'valid',
            'bulk_action' => BulkUserActionService::ACTION_CHANGE_SCOPE,
            'user_ids' => ['1'],
            'bulk_scope' => '',
        ]);

        $response = $controller->bulk($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('warning', $controller->flashes[0]['type']);
    }

    public function testBulkEnableLoginAppliesAndPersists(): void
    {
        [$controller, $manager, , , $logger, , , $bulkService] = $this->createController();
        $controller->currentUser = $this->createUser('admin');

        $userA = $this->createUser('a');
        $userB = $this->createUser('b');

        $manager->method('find')->willReturnMap([
            ['1', $userA],
            ['2', $userB],
        ]);
        $manager->expects(self::exactly(2))->method('persist');

        $bulkService
            ->expects(self::once())
            ->method('apply')
            ->with([$userA, $userB], BulkUserActionService::ACTION_ENABLE_LOGIN, null, null)
            ->willReturn(2);

        $logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Bulk user action executed',
                self::callback(static function (array $context): bool {
                    return $context['action'] === BulkUserActionService::ACTION_ENABLE_LOGIN
                        && $context['selected_ids'] === ['1', '2']
                        && $context['updated_count'] === 2
                        && $context['actor'] === 'admin';
                })
            );

        $request = new Request([], [
            '_token' => 'valid',
            'bulk_action' => BulkUserActionService::ACTION_ENABLE_LOGIN,
            'user_ids' => ['1', '2'],
        ]);

        $response = $controller->bulk($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('success', $controller->flashes[0]['type']);
    }

    /**
     * @return array{TestUserController, UserManagerInterface&MockObject, FilterQueryProvider&MockObject, PaginatorInterface&MockObject, LoggerInterface&MockObject, GroupManagerInterface&MockObject, ScopeManagerInterface&MockObject, BulkUserActionService&MockObject}
     */
    private function createController(): array
    {
        $manager = $this->createMock(UserManagerInterface::class);
        $provider = $this->createMock(FilterQueryProvider::class);
        $paginator = $this->createMock(PaginatorInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $groupManager = $this->createMock(GroupManagerInterface::class);
        $scopeManager = $this->createMock(ScopeManagerInterface::class);
        $bulkService = $this->createMock(BulkUserActionService::class);

        $controller = new TestUserController(
            $manager,
            $provider,
            $paginator,
            $logger,
            $groupManager,
            $scopeManager,
            $bulkService
        );

        return [$controller, $manager, $provider, $paginator, $logger, $groupManager, $scopeManager, $bulkService];
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->setUsername($username);

        return $user;
    }
}

final class TestUserController extends UserController
{
    public bool $csrfValid = true;
    public bool $granted = true;
    public ?User $currentUser = null;
    /** @var array<int, array{type: string, message: mixed}> */
    public array $flashes = [];

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->granted;
    }

    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfValid;
    }

    protected function addFlash(string $type, mixed $message): void
    {
        $this->flashes[] = ['type' => $type, 'message' => $message];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return new RedirectResponse('/'.$route, $status);
    }

    protected function getUser(): ?User
    {
        return $this->currentUser;
    }
}
