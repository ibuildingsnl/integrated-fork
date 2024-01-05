<?php

declare(strict_types=1);

namespace Integrated\Bundle\InstallerBundle\Migrations\MySQL;

use Doctrine\DBAL\Schema\Schema;
use Integrated\Bundle\InstallerBundle\Doctrine\ORM\Migration\AbstractMigration;
use Integrated\Bundle\UserBundle\Model\Scope;

final class Version20200615124024 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MySQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MySQLPlatform'."
        );

        $manager = $this->getEntityManager();

        if (!$scope = $manager->getRepository(Scope::class)->findOneBy(['admin' => true])) {
            $scope = new Scope();
            $scope
                ->setName('Integrated')
                ->setAdmin(true);

            $manager->persist($scope);
            $manager->flush();
        }
    }

    public function down(Schema $schema): void
    {
    }
}
