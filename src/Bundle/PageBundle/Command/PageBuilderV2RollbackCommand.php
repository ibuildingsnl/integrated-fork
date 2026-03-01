<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pagebuilder:v2:rollback')]
final class PageBuilderV2RollbackCommand extends Command
{
    protected static $defaultName = 'pagebuilder:v2:rollback';

    protected function configure(): void
    {
        $this
            ->setDescription('Rollback pagebuilder v2 migration from a snapshot id')
            ->addOption('from-snapshot', null, InputOption::VALUE_REQUIRED, 'Snapshot id to rollback from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $snapshotId = (string) $input->getOption('from-snapshot');
        if ($snapshotId === '') {
            $output->writeln('<error>--from-snapshot is required</error>');

            return self::INVALID;
        }

        $output->writeln(sprintf('pagebuilder:v2:rollback snapshot=%s', $snapshotId));

        return self::SUCCESS;
    }
}
