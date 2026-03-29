<?php

namespace Integrated\Bundle\UserBundle\Tests\Controller;

use Integrated\Bundle\UserBundle\Controller\UserController;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Integrated\Bundle\UserBundle\Model\Role;
use Integrated\Bundle\UserBundle\Model\ScopeManagerInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\UserBundle\Service\BulkUserActionService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserControllerBulkTest extends TestCase
{
    public function testIndexPassesRoleSortAndRolesFilterToProvider(): void
    {
        [$controller, , $provider, $paginator, , $groupManager, $scopeManager] = $this->createController();
        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())->method('handleRequest');
        $controller->form = $form;

        $query = new \stdClass();
        $pagination = $this->createMock(PaginationInterface::class);

        $provider->expects(self::once())
            ->method('getUsers')
            ->with(
                ['roles' => ['ROLE_ADMIN']],
                ['field' => 'role', 'direction' => 'asc']
            )
            ->willReturn($query);

        $paginator->expects(self::once())
            ->method('paginate')
            ->with($query, 2, 15)
            ->willReturn($pagination);

        $groupManager->method('findAll')->willReturn([]);
        $scopeManager->method('findAll')->willReturn([]);

        $request = new Request([
            'integrated_user_filter' => ['roles' => ['ROLE_ADMIN']],
            'sort' => 'role',
            'direction' => 'asc',
            'page' => 2,
        ]);

        $response = $controller->index($request);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('@IntegratedUser/user/index.html.twig', $controller->lastRenderedTemplate);
        self::assertSame($pagination, $controller->lastRenderedParameters['users'] ?? null);
    }

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

    public function testEnableRejectsAdministratorAccountForNonAdmin(): void
    {
        [$controller, $manager, , , $logger] = $this->createController();
        $controller->grantedAttributes = [
            'ROLE_USER_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ];

        $user = $this->createAdminUser('disabled-admin');
        $user->setEnabled(false);

        $manager->method('find')->with('1')->willReturn($user);
        $manager->expects(self::never())->method('persist');
        $logger->expects(self::never())->method('info');

        $request = new Request(['id' => '1'], ['enable_token' => 'valid'], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $response = $controller->enable($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
        self::assertFalse($user->isEnabled());
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

    public function testBulkAssignGroupRejectsAdministratorGroupForNonAdmin(): void
    {
        [$controller, $manager, , , , $groupManager, , $bulkService] = $this->createController();
        $controller->grantedAttributes = [
            'ROLE_USER_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ];

        $user = $this->createUser('u1');
        $manager->method('find')->with('1')->willReturn($user);

        $adminGroup = $this->createMock(\Integrated\Bundle\UserBundle\Model\GroupInterface::class);
        $adminGroup->method('getRoles')->willReturn(['ROLE_ADMIN']);
        $groupManager->method('find')->with('99')->willReturn($adminGroup);

        $bulkService->expects(self::never())->method('apply');

        $request = new Request([], [
            '_token' => 'valid',
            'bulk_action' => BulkUserActionService::ACTION_ASSIGN_GROUP,
            'user_ids' => ['1'],
            'bulk_group' => '99',
        ]);

        $response = $controller->bulk($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
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

    public function testBulkRejectsAdministratorTargetForNonAdmin(): void
    {
        [$controller, $manager, , , $logger, , , $bulkService] = $this->createController();
        $controller->grantedAttributes = [
            'ROLE_USER_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ];

        $adminUser = $this->createAdminUser('admin-user');
        $manager->method('find')->with('1')->willReturn($adminUser);
        $manager->expects(self::never())->method('persist');
        $bulkService->expects(self::never())->method('apply');
        $logger->expects(self::never())->method('info');

        $request = new Request([], [
            '_token' => 'valid',
            'bulk_action' => BulkUserActionService::ACTION_RESET_2FA,
            'user_ids' => ['1'],
        ]);

        $response = $controller->bulk($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
    }

    public function testDeleteAccountRejectsAdministratorAccountForNonAdmin(): void
    {
        [$controller, $manager, , , $logger] = $this->createController();
        $controller->grantedAttributes = [
            'ROLE_USER_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ];

        $adminUser = $this->createAdminUser('admin-user');
        $manager->method('find')->with('1')->willReturn($adminUser);
        $manager->expects(self::never())->method('remove');
        $logger->expects(self::never())->method('info');

        $response = $controller->deleteAccount(new Request(['id' => '1']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
    }

    public function testDeleteRejectsAdministratorAccountForNonAdmin(): void
    {
        [$controller, $manager, , , $logger] = $this->createController();
        $controller->grantedAttributes = [
            'ROLE_USER_MANAGER' => true,
            'ROLE_ADMIN' => false,
        ];

        $adminUser = $this->createAdminUser('admin-user');
        $manager->method('find')->with('1')->willReturn($adminUser);
        $manager->expects(self::never())->method('persist');
        $logger->expects(self::never())->method('info');

        $response = $controller->delete(new Request(['id' => '1']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_index', $response->getTargetUrl());
        self::assertSame('danger', $controller->flashes[0]['type']);
    }

    public function testEditRedirectsBackToEditPageAfterSuccessfulSave(): void
    {
        [$controller, $manager, , , $logger] = $this->createController();
        $controller->currentUser = $this->createUser('admin');

        $user = $this->createMock(\Integrated\Bundle\UserBundle\Model\UserInterface::class);
        $user->method('getId')->willReturn('42');
        $user->method('getUserIdentifier')->willReturn('editor');

        $form = $this->createMock(\Symfony\Component\Form\Form::class);
        $form->method('add')->willReturnSelf();
        $form->expects(self::once())->method('handleRequest');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('getClickedButton')->willReturn(null);
        $form->method('isValid')->willReturn(true);
        $controller->form = $form;

        $manager->method('find')->with('42')->willReturn($user);
        $manager->expects(self::once())->method('persist')->with($user);

        $logger->expects(self::once())->method('info')->with(
            'User updated',
            self::callback(static function (array $context): bool {
                return $context['actor'] === 'admin'
                    && $context['target_user_id'] === '42'
                    && $context['target_username'] === 'editor';
            })
        );

        $response = $controller->edit(new Request(['id' => '42'], [], [], [], [], ['REQUEST_METHOD' => 'POST']));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/integrated_user_user_edit', $response->getTargetUrl());
        self::assertSame('integrated_user_user_edit', $controller->lastRedirectRoute);
        self::assertSame(['id' => '42'], $controller->lastRedirectParameters);
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

    private function createAdminUser(string $username): User
    {
        $user = $this->createUser($username);
        $group = new Group('g-admin');
        $group->setName('Administrators');
        $group->addRole(new Role('ROLE_ADMIN', 'Administrator'));
        $user->addGroup($group);

        return $user;
    }
}

final class TestUserController extends UserController
{
    public bool $csrfValid = true;
    public bool $granted = true;
    /** @var array<string, bool> */
    public array $grantedAttributes = [];
    public ?User $currentUser = null;
    /** @var array<int, array{type: string, message: mixed}> */
    public array $flashes = [];
    public string $lastRenderedTemplate = '';
    /** @var array<string, mixed> */
    public array $lastRenderedParameters = [];
    /** @var FormInterface<mixed>|null */
    public ?FormInterface $form = null;
    public ?string $lastRedirectRoute = null;
    /** @var array<string, mixed> */
    public array $lastRedirectParameters = [];

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        if (\is_string($attribute) && \array_key_exists($attribute, $this->grantedAttributes)) {
            return $this->grantedAttributes[$attribute];
        }

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
        $this->lastRedirectRoute = $route;
        $this->lastRedirectParameters = $parameters;

        return new RedirectResponse('/'.$route, $status);
    }

    protected function getUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return '/'.$route;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface
    {
        if ($this->form === null) {
            throw new \RuntimeException('Test form is not configured.');
        }

        return $this->form;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastRenderedTemplate = $view;
        $this->lastRenderedParameters = $parameters;

        return $response ?? new Response();
    }
}
