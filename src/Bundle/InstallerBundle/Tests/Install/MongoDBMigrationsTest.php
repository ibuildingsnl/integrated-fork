<?php

declare(strict_types=1);

namespace Integrated\Bundle\InstallerBundle\Tests\Install;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ImportBundle\Migrations\MongoDB\Version20260224193000;
use Integrated\Bundle\InstallerBundle\Install\MongoDBMigrations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class MongoDBMigrationsTest extends TestCase
{
    public function testMigrationSourcesIncludeImportBundleMongoMigrations(): void
    {
        $projectDir = dirname(__DIR__, 8);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('getParameter')
            ->with('kernel.project_dir')
            ->willReturn($projectDir);

        $migrations = new MongoDBMigrations(
            $this->createMock(DocumentManager::class),
            $container
        );
        $importMigrationsDirectory = realpath($projectDir.'/vendor/twindigital/integrated-import-bundle/src/Migrations/MongoDB');

        $method = new \ReflectionMethod(MongoDBMigrations::class, 'getMigrationSources');
        $method->setAccessible(true);

        $sources = $method->invoke($migrations);

        self::assertIsArray($sources);
        self::assertContains(
            [
                'namespace' => 'Integrated\\Bundle\\ImportBundle\\Migrations\\MongoDB',
                'directory' => $importMigrationsDirectory,
            ],
            $sources
        );
        self::assertContains(
            [
                'namespace' => (new \ReflectionClass(Version20260224193000::class))->getNamespaceName(),
                'directory' => $importMigrationsDirectory,
            ],
            $sources
        );
    }
}
