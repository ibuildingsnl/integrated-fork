<?php

declare(strict_types=1);

namespace Integrated\Bundle\InstallerBundle\Tests\Install;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\InstallerBundle\Install\MongoDBMigrations;
use PHPUnit\Framework\TestCase;

final class MongoDBMigrationsTest extends TestCase
{
    public function testIntegratedMongoMigrationsStayScopedToInstallerBundle(): void
    {
        $migrations = new MongoDBMigrations(
            $this->createMock(DocumentManager::class)
        );

        $reflection = new \ReflectionClass($migrations);
        $constantDirectory = $reflection->getConstant('DOCTRINE_MIGRATIONS_DIRECTORY');
        $constantNamespace = $reflection->getConstant('DOCTRINE_MIGRATIONS_NAMESPACE');

        self::assertSame('../Migrations/MongoDB', ltrim((string) $constantDirectory, '/'));
        self::assertSame('Integrated\\Bundle\\InstallerBundle\\Migrations\\MongoDB', $constantNamespace);
        self::assertFalse($reflection->hasConstant('IMPORT_BUNDLE_MIGRATIONS_DIRECTORY'));
        self::assertFalse($reflection->hasConstant('IMPORT_BUNDLE_MIGRATIONS_NAMESPACE'));

        $source = file_get_contents($reflection->getFileName());

        self::assertIsString($source);
        self::assertStringNotContainsString('integrated-import-bundle', $source);
        self::assertStringNotContainsString('Integrated\\Bundle\\ImportBundle', $source);
    }
}
