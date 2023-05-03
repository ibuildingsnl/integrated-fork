<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\LockingBundle\Command;

use Integrated\Common\Locks\ManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'locking:dbal:clean',
    description: 'Clean up the expired locks',
)]
class LockingDBALCleanUpCommand extends Command
{
    private ManagerInterface $manager;

    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp(<<<EOF
The <info>%command.name%</info> removes all the expired locks stored in the database

<info>php %command.full_name%</info>
EOF
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (method_exists($this->manager, 'clean')) {
            $this->manager->clean();
        }

        return self::SUCCESS;
    }
}
