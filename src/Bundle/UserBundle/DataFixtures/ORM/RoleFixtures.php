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
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\UserBundle\Model\Role;

class RoleFixtures extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createRole('ROLE_ADMIN', 'Administrator'));
        $manager->persist($this->createRole('ROLE_USER_MANAGER', 'User manager'));
        $manager->persist($this->createRole('ROLE_WEBSITE_MANAGER', 'Website manager'));
        $manager->persist($this->createRole('ROLE_CHANNEL_MANAGER', 'Channel manager'));

        $manager->flush();
    }

    private function createRole(string $role, string $label): Role
    {
        $role = new Role($role, $label);

        $this->setReference($role->getRole(), $role);

        return $role;
    }
}
