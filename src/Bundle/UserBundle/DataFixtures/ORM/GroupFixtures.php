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
use Integrated\Bundle\UserBundle\Model\Role;

class GroupFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $group = new Group();

        $group->setName('Administrators');
        $group->addRole($this->getReference('ROLE_ADMIN', Role::class));

        $this->setReference(strtolower($group->getName()), $group);

        $manager->persist($group);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            RoleFixtures::class,
        ];
    }
}
