<?php

declare(strict_types=1);

namespace Integrated\Bundle\InstallerBundle\Migrations\MySQL;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\InstallerBundle\Doctrine\EntityManagerAwareInterface;
use Integrated\Bundle\UserBundle\Model\Scope;

final class Version20200615124024 extends AbstractMigration implements EntityManagerAwareInterface
{
    private EntityManagerInterface $manager;

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MySQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MySQLPlatform'."
        );

        if (!$scope = $this->manager->getRepository(Scope::class)->findOneBy(['admin' => true])) {
            $scope = new Scope();
            $scope
                ->setName('Integrated')
                ->setAdmin(true);

            $this->manager->persist($scope);
            $this->manager->flush();
        }
    }

    public function down(Schema $schema): void
    {
    }

    public function setEntityManager(?EntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }
}
