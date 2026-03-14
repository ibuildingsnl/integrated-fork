<?php

namespace Integrated\Bundle\InstallerBundle\Install;

use AntiMattr\MongoDB\Migrations\Configuration\Configuration;
use AntiMattr\MongoDB\Migrations\Migration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MongoDBMigrations
{
    public const DOCTRINE_MIGRATIONS_DIRECTORY = '/../Migrations/MongoDB';
    public const DOCTRINE_MIGRATIONS_NAMESPACE = 'Integrated\Bundle\InstallerBundle\Migrations\MongoDB';
    public const IMPORT_BUNDLE_MIGRATIONS_NAMESPACE = 'Integrated\Bundle\ImportBundle\Migrations\MongoDB';
    public const IMPORT_BUNDLE_MIGRATIONS_DIRECTORY = 'vendor/twindigital/integrated-import-bundle/src/Migrations/MongoDB';
    public const DOCTRINE_MIGRATIONS_NAME = 'Integrated MongoDB Migrations';
    public const DOCTRINE_MIGRATIONS_COLLECTION = 'integrated_migration_versions';
    public const DOCTRINE_MIGRATIONS_DIRECTION_UP = 'up';

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Migrations constructor.
     */
    public function __construct(DocumentManager $documentManager, ContainerInterface $container)
    {
        $this->documentManager = $documentManager;
        $this->container = $container;
    }

    /**
     * Execute migrations.
     */
    public function execute()
    {
        $sources = $this->getMigrationSources();
        $primarySource = $sources[0] ?? null;

        $configuration = new Configuration($this->documentManager->getClient());
        $configuration->setMigrationsCollectionName(self::DOCTRINE_MIGRATIONS_COLLECTION);
        $configuration->setMigrationsDatabaseName($this->documentManager->getDocumentDatabase(Content::class)->getDatabaseName());
        $configuration->setName(self::DOCTRINE_MIGRATIONS_NAME);
        if ($primarySource) {
            $configuration->setMigrationsDirectory($primarySource['directory']);
            $configuration->setMigrationsNamespace($primarySource['namespace']);
        }

        foreach ($sources as $source) {
            $configuration->setMigrationsNamespace($source['namespace']);
            $configuration->registerMigrationsFromDirectory($source['directory']);
        }

        $to = $configuration->getLatestVersion();
        $versions = $configuration->getMigrationsToExecute(self::DOCTRINE_MIGRATIONS_DIRECTION_UP, $to);
        foreach ($versions as $version) {
            $migration = $version->getMigration();
        }

        $migration = new Migration($configuration);
        $migration->migrate();
    }

    /**
     * @return array<int, array{namespace: string, directory: string}>
     */
    private function getMigrationSources(): array
    {
        return [
            [
                'namespace' => self::DOCTRINE_MIGRATIONS_NAMESPACE,
                'directory' => realpath(__DIR__.self::DOCTRINE_MIGRATIONS_DIRECTORY),
            ],
            [
                'namespace' => self::IMPORT_BUNDLE_MIGRATIONS_NAMESPACE,
                'directory' => $this->resolveProjectPath(self::IMPORT_BUNDLE_MIGRATIONS_DIRECTORY),
            ],
        ];
    }

    private function resolveProjectPath(string $path): string
    {
        $projectDir = (string) $this->container->getParameter('kernel.project_dir');

        return (string) realpath(rtrim($projectDir, '/').'/'.$path);
    }
}
