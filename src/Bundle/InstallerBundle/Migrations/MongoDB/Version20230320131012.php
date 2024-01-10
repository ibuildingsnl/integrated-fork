<?php

namespace Integrated\Bundle\InstallerBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\InstallerBundle\Migrator\ImageMigrator;
use MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version20230320131012 extends AbstractMigration
{
    public function __construct(private DocumentManager $manager)
    {
    }

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
        $migrator = new ImageMigrator($this->manager);

        $migrator->move(Channel::class, 'logo');
        $migrator->move(Company::class, 'logo');
        $migrator->move(Person::class, 'picture');
    }

    public function down(Database $db)
    {
        $this->throwIrreversibleMigrationException();
    }
}
