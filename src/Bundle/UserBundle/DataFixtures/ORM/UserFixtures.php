<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserFixtures extends AbstractFixture implements DependentFixtureInterface
{
    private PasswordHasherFactoryInterface $factory;

    public function __construct(PasswordHasherFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();

        $user->setUsername('admin@example.com');
        $user->setPassword($this->factory->getPasswordHasher($user)->hash('admin'));
        $user->setGroups([$this->getReference('administrators', Group::class)]);
        $user->setScope($this->getReference('integrated', Scope::class));

        $manager->persist($user);

        $user = new User();

        $user->setUsername('public@example.com');
        $user->setPassword($this->factory->getPasswordHasher($user)->hash('public'));
        $user->setScope($this->getReference('public', Scope::class));

        $manager->persist($user);

        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GroupFixtures::class,
            ScopeFixtures::class,
        ];
    }
}
