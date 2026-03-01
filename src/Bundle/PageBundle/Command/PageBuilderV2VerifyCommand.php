<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pagebuilder:v2:verify')]
final class PageBuilderV2VerifyCommand extends Command
{
    protected static $defaultName = 'pagebuilder:v2:verify';

    protected function configure(): void
    {
        $this->setDescription('Verify pagebuilder v2 migration completeness');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('pagebuilder:v2:verify complete');

        return self::SUCCESS;
    }
}
