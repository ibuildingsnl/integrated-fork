<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Security;

use Integrated\Bundle\UserBundle\Context\ScopeContext;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Security\UserProvider;
use Integrated\Bundle\UserBundle\Security\UserScopeProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class UserProviderTest extends TestCase
{
    public function testLoadUserByIdentifierUsesUsernameOrEmailLookup(): void
    {
        $manager = $this->createMock(UserManagerInterface::class);
        $manager
            ->expects(self::once())
            ->method('getClassName')
            ->willReturn(User::class);

        $user = new User();
        $user->setUsername('bas@twindigital.nl');

        $manager
            ->expects(self::once())
            ->method('findEnabledByUsernameOrEmailAndScope')
            ->willReturnCallback(static function (string $identifier, mixed $scope = null) use ($user) {
                self::assertSame('bas@twindigital.nl', $identifier);
                self::assertNull($scope);

                return $user;
            });

        $provider = new UserProvider($manager);

        self::assertSame($user, $provider->loadUserByIdentifier('bas@twindigital.nl'));
    }

    public function testLoadUserByIdentifierThrowsUserNotFoundWithIdentifier(): void
    {
        $manager = $this->createMock(UserManagerInterface::class);
        $manager
            ->expects(self::once())
            ->method('getClassName')
            ->willReturn(User::class);

        $manager
            ->expects(self::once())
            ->method('findEnabledByUsernameOrEmailAndScope')
            ->willReturnCallback(static function (string $identifier, mixed $scope = null) {
                self::assertSame('unknown@example.com', $identifier);
                self::assertNull($scope);

                return null;
            });

        $provider = new UserProvider($manager);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('identifier');

        $provider->loadUserByIdentifier('unknown@example.com');
    }

    public function testScopeProviderUsesUsernameOrEmailLookupWithinScope(): void
    {
        $manager = $this->createMock(UserManagerInterface::class);
        $manager
            ->expects(self::once())
            ->method('getClassName')
            ->willReturn(User::class);

        $scope = new Scope();
        $context = $this->createMock(ScopeContext::class);
        $context
            ->expects(self::once())
            ->method('getScope')
            ->willReturn($scope);

        $user = new User();
        $user->setUsername('yolinde');

        $manager
            ->expects(self::once())
            ->method('findEnabledByUsernameOrEmailAndScope')
            ->willReturnCallback(static function (string $identifier, mixed $scopeArg = null) use ($user, $scope) {
                self::assertSame('yolinde@daily.nl', $identifier);
                self::assertSame($scope, $scopeArg);

                return $user;
            });

        $provider = new UserScopeProvider($manager, $context);

        self::assertSame($user, $provider->loadUserByIdentifier('yolinde@daily.nl'));
    }
}
