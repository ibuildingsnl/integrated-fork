<?php

namespace Integrated\Bundle\ContentBundle\Tests\Fixtures;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TestEntityManagerFactory
{
    public static function create(): EntityManagerInterface
    {
        if (!\extension_loaded('pdo_sqlite')) {
            TestCase::markTestSkipped('Extension pdo_sqlite is required.');
        }

        $config = self::createConfiguration();

        return new EntityManager(
            DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ], $config),
            $config
        );
    }

    private static function createConfiguration(): Configuration
    {
        $config = new Configuration();
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Integrated\Bundle\ContentBundle\Tests');

        return $config;
    }
}
