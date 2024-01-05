<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\InstallerBundle\Command;

use Integrated\Bundle\InstallerBundle\Install\MongoDBMigrations;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'integrated:install:mongodb:migrate',
    hidden: true,
)]
class IntegratedMongoDBMigrateCommand extends Command
{
    private MongoDBMigrations $migrations;

    public function __construct(MongoDBMigrations $migrations)
    {
        $this->migrations = $migrations;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->migrations->execute();

        return self::SUCCESS;
    }
}
