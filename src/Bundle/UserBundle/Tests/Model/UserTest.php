<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Model;

use Integrated\Bundle\UserBundle\Model\Role;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testGetUserIdentifierReturnsEmptyStringWhenUsernameIsMissing(): void
    {
        $user = new User();

        self::assertSame('', $user->getUserIdentifier());
    }

    public function testIsEqualToReturnsFalseWhenRolesChange(): void
    {
        $current = $this->createUserWithRoleAndScope('ROLE_ADMIN', true);
        $refreshed = $this->createUserWithRoleAndScope('ROLE_USER_MANAGER', true);

        self::assertFalse($current->isEqualTo($refreshed));
    }

    public function testIsEqualToReturnsFalseWhenScopePrivilegesChange(): void
    {
        $current = $this->createUserWithRoleAndScope('ROLE_USER_MANAGER', true);
        $refreshed = $this->createUserWithRoleAndScope('ROLE_USER_MANAGER', false);

        self::assertFalse($current->isEqualTo($refreshed));
    }

    public function testIsEqualToReturnsTrueWhenSecurityStateMatches(): void
    {
        $current = $this->createUserWithRoleAndScope('ROLE_USER_MANAGER', true);
        $refreshed = $this->createUserWithRoleAndScope('ROLE_USER_MANAGER', true);

        self::assertTrue($current->isEqualTo($refreshed));
    }

    public function testGetRolesHandlesUninitializedCollections(): void
    {
        $user = new User();
        $user->__unserialize(['123', 'editor@example.test', 'hashed-password', 'salt']);

        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertSame([], $user->getGroups());
    }

    public function testSerializedUserStaysEqualUntilSecurityStateChanges(): void
    {
        $current = $this->createUserWithRoleAndScope('ROLE_ADMIN', true);

        $sessionUser = new User();
        $sessionUser->__unserialize($current->__serialize());

        self::assertTrue($sessionUser->isEqualTo($current));

        $changed = $this->createUserWithRoleAndScope('ROLE_USER_MANAGER', true);

        self::assertFalse($sessionUser->isEqualTo($changed));
    }

    private function createUserWithRoleAndScope(string $role, bool $isAdminScope): User
    {
        $scope = (new Scope())
            ->setName('Integrated')
            ->setAdmin($isAdminScope);

        $user = new User();
        $user->setUsername('editor@example.test');
        $user->setPassword('hashed-password');
        $user->setSalt('salt');
        $user->setScope($scope);
        $user->addRole(new Role($role, $role));

        return $user;
    }
}
