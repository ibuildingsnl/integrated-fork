<?php

namespace Integrated\Bundle\ContentBundle\Tests\Fixtures;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use PHPUnit\Framework\TestCase;

class TestEntityManagerFactory
{
    public static function create(): Connection
    {
        if (!\extension_loaded('pdo_sqlite')) {
            TestCase::markTestSkipped('Extension pdo_sqlite is required.');
        }

        return DriverManager::getConnection(
            [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            self::createConfiguration()
        );
    }

    private static function createConfiguration(): Configuration
    {
        $config = new Configuration();
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setProxyNamespace('Integrated\Bundle\ContentBundle\Tests');

        return $config;
    }
}
