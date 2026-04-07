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
    public function testAuthenticateSkipsTokenRewriteWhenScopeRoleAlreadyPresent(): void
    {
        $user = $this->createAdminUser();
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_ADMIN', 'ROLE_SCOPE_INTEGRATED']);

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

    public function testAuthenticateAddsScopeRoleWhenMissing(): void
    {
        $user = $this->createAdminUser();
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_ADMIN']);

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
                self::assertContains('ROLE_ADMIN', $newToken->getRoleNames());
                self::assertContains('ROLE_SCOPE_INTEGRATED', $newToken->getRoleNames());

                return true;
            }));

        $listener = new ScopeListener($storage, 'main');
        $listener->authenticate($this->createEvent());
    }

    private function createAdminUser(): User
    {
        $scope = (new Scope())->setAdmin(true);
        $user = new User();
        $user->setScope($scope);
        $user->addRole(new Role('ROLE_ADMIN', 'Administrator'));

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
