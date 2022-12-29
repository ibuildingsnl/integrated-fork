<?php

namespace Integrated\Bundle\InstallerBundle\Doctrine\ODM\Migration;

use AntiMattr\MongoDB\Migrations;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractMigration extends Migrations\AbstractMigration implements ContainerAwareInterface
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->container->get('doctrine_mongodb.odm.document_manager');
    }
}
