<?php

namespace Integrated\Bundle\UserBundle\Tests\Form\Type;

use Integrated\Bundle\UserBundle\Form\Type\GroupType;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GroupTypeTest extends TestCase
{
    public function testAdminGroupIsFilteredWhenAuthorizationCheckerIsMissing(): void
    {
        $manager = $this->createMock(GroupManagerInterface::class);
        $manager->method('getClassName')->willReturn('Integrated\\Bundle\\UserBundle\\Model\\Group');

        $type = new GroupType($manager);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertIsCallable($options['choice_filter']);
        self::assertFalse(($options['choice_filter'])($this->createGroupWithRoles(['ROLE_ADMIN'])));
        self::assertTrue(($options['choice_filter'])($this->createGroupWithRoles(['ROLE_USER'])));
    }

    public function testAdminGroupIsFilteredForNonAdmins(): void
    {
        $manager = $this->createMock(GroupManagerInterface::class);
        $manager->method('getClassName')->willReturn('Integrated\\Bundle\\UserBundle\\Model\\Group');

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $type = new GroupType($manager, $authorizationChecker);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertIsCallable($options['choice_filter']);
        self::assertFalse(($options['choice_filter'])($this->createGroupWithRoles(['ROLE_ADMIN'])));
        self::assertTrue(($options['choice_filter'])($this->createGroupWithRoles(['ROLE_USER'])));
    }

    public function testAdminGroupIsVisibleForAdmins(): void
    {
        $manager = $this->createMock(GroupManagerInterface::class);
        $manager->method('getClassName')->willReturn('Integrated\\Bundle\\UserBundle\\Model\\Group');

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        $type = new GroupType($manager, $authorizationChecker);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        self::assertNull($options['choice_filter']);
    }

    /**
     * @param array<int, string> $roles
     */
    private function createGroupWithRoles(array $roles): object
    {
        return new class($roles) {
            /**
             * @param array<int, string> $roles
             */
            public function __construct(private readonly array $roles)
            {
            }

            /**
             * @return array<int, string>
             */
            public function getRoles(): array
            {
                return $this->roles;
            }
        };
    }
}
