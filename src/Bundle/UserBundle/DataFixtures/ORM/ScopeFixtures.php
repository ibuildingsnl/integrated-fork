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
use Integrated\Bundle\UserBundle\Model\Scope;

class ScopeFixtures extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createScope('Integrated', true));
        $manager->persist($this->createScope('Public', false));

        $manager->flush();
    }

    private function createScope(string $name, bool $admin): Scope
    {
        $scope = new Scope();

        $scope->setName($name);
        $scope->setAdmin($admin);

        $this->setReference(strtolower($scope->getName()), $scope);

        return $scope;
    }
}
