<?php

declare(strict_types=1);

namespace Integrated\Bundle\InstallerBundle\Migrations\MySQL;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200615124024 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO security_scopes (name, admin)
             SELECT ?, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM security_scopes WHERE admin = 1)',
            ['Integrated']
        );
    }

    public function down(Schema $schema): void
    {
    }
}
