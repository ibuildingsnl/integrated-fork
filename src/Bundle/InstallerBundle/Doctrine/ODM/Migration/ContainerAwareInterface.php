<?php

namespace Integrated\Bundle\InstallerBundle\Doctrine\ODM\Migration;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Replaces Symfony's ContainerAwareInterface, which was removed in Symfony 7.0.
 *
 * Migrations are instantiated by the migrations library instead of by the
 * service container, so they cannot use constructor injection.
 *
 * @see \Integrated\Bundle\InstallerBundle\Install\MongoDBMigrations
 */
interface ContainerAwareInterface
{
    public function setContainer(?ContainerInterface $container = null): void;
}
