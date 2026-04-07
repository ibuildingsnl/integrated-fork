<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Security\Firewall;

use Integrated\Bundle\UserBundle\Model\Role;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Security\Firewall\ScopeListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class ScopeListenerTest extends TestCase
{
    public function testAuthenticateSkipsTokenRewriteWhenTokenAlreadyMatchesExpectedRoles(): void
    {
        $user = $this->createAdminUser();
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_ADMIN', 'ROLE_SCOPE_INTEGRATED', 'ROLE_USER']);

        $storage = $this->createMock(TokenStorageInterface::class);
        $storage
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $storage
            ->expects(self::never())
            ->method('setToken');

        $listener = new ScopeListener($storage, 'main');
        $listener->authenticate($this->createEvent());
    }

    public function testAuthenticateRefreshesTokenWhenUserRolesChanged(): void
    {
        $user = $this->createAdminUser('ROLE_USER_MANAGER');
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_ADMIN', 'ROLE_SCOPE_INTEGRATED', 'ROLE_USER']);
        $token->setAttribute('test', 'kept');

        $storage = $this->createMock(TokenStorageInterface::class);
        $storage
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $storage
            ->expects(self::once())
            ->method('setToken')
            ->with(self::callback(static function (UsernamePasswordToken $newToken) use ($user): bool {
                self::assertSame($user, $newToken->getUser());
                self::assertSame(['ROLE_SCOPE_INTEGRATED', 'ROLE_USER', 'ROLE_USER_MANAGER'], $newToken->getRoleNames());
                self::assertSame('kept', $newToken->getAttribute('test'));

                return true;
            }));

        $listener = new ScopeListener($storage, 'main');
        $listener->authenticate($this->createEvent());
    }

    public function testAuthenticateRemovesIntegratedScopeRoleWhenScopeIsNoLongerAdmin(): void
    {
        $scope = (new Scope())->setAdmin(false);
        $user = new User();
        $user->setScope($scope);
        $user->addRole(new Role('ROLE_USER_MANAGER', 'User manager'));

        $token = new UsernamePasswordToken($user, 'main', ['ROLE_SCOPE_INTEGRATED', 'ROLE_USER', 'ROLE_USER_MANAGER']);

        $storage = $this->createMock(TokenStorageInterface::class);
        $storage
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $storage
            ->expects(self::once())
            ->method('setToken')
            ->with(self::callback(static function (UsernamePasswordToken $newToken) use ($user): bool {
                self::assertSame($user, $newToken->getUser());
                self::assertSame(['ROLE_USER', 'ROLE_USER_MANAGER'], $newToken->getRoleNames());

                return true;
            }));

        $listener = new ScopeListener($storage, 'main');
        $listener->authenticate($this->createEvent());
    }

    public function testAuthenticateLogsUserOutWhenAccountIsDisabled(): void
    {
        $user = $this->createAdminUser();
        $user->setEnabled(false);

        $token = new UsernamePasswordToken($user, 'main', ['ROLE_ADMIN', 'ROLE_SCOPE_INTEGRATED', 'ROLE_USER']);

        $storage = $this->createMock(TokenStorageInterface::class);
        $storage
            ->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $storage
            ->expects(self::once())
            ->method('setToken')
            ->with(null);

        $listener = new ScopeListener($storage, 'main');
        $listener->authenticate($this->createEvent());
    }

    private function createAdminUser(string $role = 'ROLE_ADMIN'): User
    {
        $scope = (new Scope())->setAdmin(true);
        $user = new User();
        $user->setScope($scope);
        $user->addRole(new Role($role, 'Administrator'));

        return $user;
    }

    private function createEvent(): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('https://example.test/admin/content/new?type=article'),
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
