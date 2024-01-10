<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\InstallerBundle\Doctrine;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerAwareMigrationFactory implements MigrationFactory
{
    public function __construct(private MigrationFactory $factory, private EntityManagerInterface $manager)
    {
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $migration = $this->factory->createVersion($migrationClassName);

        if ($migration instanceof EntityManagerAwareInterface) {
            $migration->setEntityManager($this->manager);
        }

        return $migration;
    }
}
