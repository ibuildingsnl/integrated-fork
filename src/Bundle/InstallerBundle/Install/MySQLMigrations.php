<?php

namespace Integrated\Bundle\InstallerBundle\Install;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\ORM\EntityManager;

class MySQLMigrations
{
    public const DOCTRINE_MIGRATIONS_DIRECTORY = '/../Migrations/MySQL';
    public const DOCTRINE_MIGRATIONS_NAMESPACE = 'Integrated\Bundle\InstallerBundle\Migrations\MySQL';
    public const DOCTRINE_MIGRATIONS_NAME = 'Integrated MySQL Migrations';
    public const DOCTRINE_MIGRATIONS_TABLE = 'integrated_migration_versions';
    public const DOCTRINE_MIGRATIONS_DIRECTION_UP = 'up';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Migrations constructor.
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        $directory = realpath(__DIR__.self::DOCTRINE_MIGRATIONS_DIRECTORY);

        $configuration = new ConfigurationArray([
            'migrations_paths' => [
                self::DOCTRINE_MIGRATIONS_NAMESPACE => $directory,
            ],
            'table_storage' => [
                'table_name' => self::DOCTRINE_MIGRATIONS_TABLE,
            ],
        ]);

        $dependencyFactory = DependencyFactory::fromEntityManager(
            $configuration,
            new ExistingEntityManager($this->entityManager)
        );

        $dependencyFactory->getMetadataStorage()->ensureInitialized();

        $version = $dependencyFactory->getVersionAliasResolver()->resolveVersionAlias('latest');
        $plan = $dependencyFactory->getMigrationPlanCalculator()->getPlanUntilVersion($version);

        if (\count($plan) === 0) {
            return;
        }

        $dependencyFactory->getMigrator()->migrate($plan, new MigratorConfiguration());
    }
}
