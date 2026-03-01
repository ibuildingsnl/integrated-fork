<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PageBuilderV2MigrateCommand extends Command
{
    protected static $defaultName = 'pagebuilder:v2:migrate';

    protected function configure(): void
    {
        $this
            ->setDescription('Run pagebuilder v2 migration (dry-run or execute)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate migration mappings without writing')
            ->addOption('execute', null, InputOption::VALUE_NONE, 'Execute migration writes')
            ->addOption('batch', null, InputOption::VALUE_REQUIRED, 'Batch size', '250')
            ->addOption('resume-from', null, InputOption::VALUE_REQUIRED, 'Resume from migration cursor', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $execute = (bool) $input->getOption('execute');

        if ($dryRun === $execute) {
            $output->writeln('<error>Choose exactly one mode: --dry-run or --execute</error>');

            return self::INVALID;
        }

        $mode = $dryRun ? 'dry-run' : 'execute';
        $batch = (string) $input->getOption('batch');
        $resumeFrom = (string) $input->getOption('resume-from');

        $output->writeln(sprintf(
            'pagebuilder:v2:migrate mode=%s batch=%s resume-from=%s',
            $mode,
            $batch,
            $resumeFrom !== '' ? $resumeFrom : '<start>'
        ));

        return self::SUCCESS;
    }
}

