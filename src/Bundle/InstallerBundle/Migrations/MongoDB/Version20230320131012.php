<?php

namespace Integrated\Bundle\InstallerBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\InstallerBundle\Migrator\ImageMigrator;
use MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version20230320131012 extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Convert image files to content';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Database $db)
    {
        $migrator = new ImageMigrator($this->container->get('doctrine_mongodb.odm.document_manager'));

        $migrator->move(Channel::class, 'logo');
        $migrator->move(Company::class, 'logo');
        $migrator->move(Person::class, 'picture');
    }

    public function down(Database $db)
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
