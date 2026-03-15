<?php

namespace Integrated\Bundle\InstallerBundle\Install;

use AntiMattr\MongoDB\Migrations\Configuration\Configuration;
use AntiMattr\MongoDB\Migrations\Migration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;

class MongoDBMigrations
{
    public const DOCTRINE_MIGRATIONS_DIRECTORY = '/../Migrations/MongoDB';
    public const DOCTRINE_MIGRATIONS_NAMESPACE = 'Integrated\Bundle\InstallerBundle\Migrations\MongoDB';
    public const DOCTRINE_MIGRATIONS_NAME = 'Integrated MongoDB Migrations';
    public const DOCTRINE_MIGRATIONS_COLLECTION = 'integrated_migration_versions';
    public const DOCTRINE_MIGRATIONS_DIRECTION_UP = 'up';

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * Migrations constructor.
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Execute migrations.
     */
    public function execute()
    {
        $directory = realpath(__DIR__.self::DOCTRINE_MIGRATIONS_DIRECTORY);
        if ($directory === false) {
            throw new \RuntimeException('Integrated MongoDB migrations directory is missing.');
        }

        $configuration = new Configuration($this->documentManager->getClient());
        $configuration->setMigrationsCollectionName(self::DOCTRINE_MIGRATIONS_COLLECTION);
        $configuration->setMigrationsDatabaseName($this->documentManager->getDocumentDatabase(Content::class)->getDatabaseName());
        $configuration->setName(self::DOCTRINE_MIGRATIONS_NAME);
        $configuration->setMigrationsDirectory($directory);
        $configuration->setMigrationsNamespace(self::DOCTRINE_MIGRATIONS_NAMESPACE);
        $configuration->registerMigrationsFromDirectory($directory);

        $to = $configuration->getLatestVersion();
        $versions = $configuration->getMigrationsToExecute(self::DOCTRINE_MIGRATIONS_DIRECTION_UP, $to);
        foreach ($versions as $version) {
            $migration = $version->getMigration();
        }

        $migration = new Migration($configuration);
        $migration->migrate();
    }
}
