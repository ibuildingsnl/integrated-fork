<?php

namespace Integrated\Bundle\UserBundle\Tests\Form\Type;

use Integrated\Bundle\UserBundle\Doctrine\RoleManager;
use Integrated\Bundle\UserBundle\Form\Type\RoleType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleTypeTest extends TestCase
{
    public function testRoleAdminIsHiddenWhenAuthorizationCheckerIsMissing(): void
    {
        $manager = $this->createMock(RoleManager::class);
        $manager->method('getRolesFromSources')->willReturn([
            'ROLE_ADMIN' => 'Administrator',
            'ROLE_USER_MANAGER' => 'User manager',
        ]);

        $type = new RoleType($manager);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertSame('ROLE_USER_MANAGER', $options['choices']['User manager']);
        self::assertArrayNotHasKey('Administrator', $options['choices']);
    }

    public function testRoleAdminIsHiddenForNonAdminUsers(): void
    {
        $manager = $this->createMock(RoleManager::class);
        $manager->method('getRolesFromSources')->willReturn([
            'ROLE_ADMIN' => 'Administrator',
            'ROLE_USER_MANAGER' => 'User manager',
        ]);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $type = new RoleType($manager, $authorizationChecker);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertSame('ROLE_USER_MANAGER', $options['choices']['User manager']);
        self::assertArrayNotHasKey('Administrator', $options['choices']);
    }

    public function testRoleAdminIsVisibleForAdmins(): void
    {
        $manager = $this->createMock(RoleManager::class);
        $manager->method('getRolesFromSources')->willReturn([
            'ROLE_ADMIN' => 'Administrator',
        ]);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        $type = new RoleType($manager, $authorizationChecker);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertSame('ROLE_ADMIN', $options['choices']['Administrator']);
    }
}
