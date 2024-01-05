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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareMigrationFactory implements MigrationFactory
{
    private MigrationFactory $factory;
    private ContainerInterface $container;

    public function __construct(MigrationFactory $factory, ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $migration = $this->factory->createVersion($migrationClassName);

        if ($migration instanceof ContainerAwareInterface) {
            $migration->setContainer($this->container);
        }

        return $migration;
    }
}
