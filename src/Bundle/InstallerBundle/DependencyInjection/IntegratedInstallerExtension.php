<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\InstallerBundle\DependencyInjection;

use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Version\MigrationFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IntegratedInstallerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('command.xml');
        $loader->load('command.doctrine.xml');
        $loader->load('doctrine.xml');

        $definition = $container->getDefinition('integrated_installer.doctrine.migrations.dependency_factory');
        $definition->addMethodCall('setDefinition', [
            MigrationFactory::class,
            new ServiceClosureArgument(new Reference('integrated_installer.doctrine.migrations.migrations_factory')),
        ]);

        $definition = $container->getDefinition('integrated_installer.doctrine.migrations.configuration');

        $definition->addMethodCall('addMigrationsDirectory', ['Integrated\Bundle\InstallerBundle\Migrations\MySQL', \dirname(__DIR__).'/Migrations/MySQL']);
        $definition->addMethodCall('setMetadataStorageConfiguration', [$this->getTableMetadataStorage()]);
    }

    private function getTableMetadataStorage(): Definition
    {
        $definition = new Definition(TableMetadataStorageConfiguration::class);
        $definition->addMethodCall('setTableName', ['integrated_migration_versions']);

        return $definition;
    }
}
