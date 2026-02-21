<?php

declare(strict_types=1);

namespace Integrated\Bundle\InstallerBundle\Migrations\MySQL;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Integrated\Bundle\ChannelBundle\Model\Options;

final class Version20230331114632 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\AbstractMySQLPlatform'."
        );

        $this->addSql('ALTER TABLE channel_connector_config CHANGE options options LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', CHANGE channels channels LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');

        foreach ($this->connection->fetchAllAssociative('SELECT id, options FROM channel_connector_config') as $row) {
            $this->addSql('UPDATE channel_connector_config SET options = ? WHERE id = ?', [json_encode(unserialize($row['options'])), $row['id']]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\AbstractMySQLPlatform'."
        );

        $this->addSql('ALTER TABLE channel_connector_config CHANGE options options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', CHANGE channels channels LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin` COMMENT \'(DC2Type:json)\'');

        foreach ($this->connection->fetchAllAssociative('SELECT id, options FROM channel_connector_config') as $row) {
            $this->addSql('UPDATE channel_connector_config SET options = ? WHERE id = ?', [serialize(new Options(json_decode($row['options'], true))), $row['id']]);
        }
    }
}
