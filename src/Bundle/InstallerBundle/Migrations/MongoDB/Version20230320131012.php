<?php

namespace Integrated\Bundle\InstallerBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\InstallerBundle\Migrator\ImageMigrator;
use MongoDB\Database;

final class Version20230320131012 extends AbstractMigration
{
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
        $migrator = new ImageMigrator($db);

        $migrator->move('channel', null, 'logo');
        $migrator->move('content', Company::class, 'logo');
        $migrator->move('content', Person::class, 'picture');
    }

    public function down(Database $db)
    {
        $this->throwIrreversibleMigrationException();
    }
}
