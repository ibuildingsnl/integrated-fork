<?php

namespace Integrated\Bundle\InstallerBundle\Command;

use Integrated\Bundle\InstallerBundle\Test\BundleTest;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'integrated:install',
    description: 'Run the Integrated installer to set up database scheme etc.',
)]
class IntegratedInstallCommand extends Command
{
    private Client $solrClient;
    private BundleTest $bundleTest;
    private KernelInterface $kernel;
    private ?string $php = null;

    public function __construct(Client $solrClient, BundleTest $bundleTest, KernelInterface $kernel)
    {
        $this->solrClient = $solrClient;
        $this->bundleTest = $bundleTest;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'step',
            's',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Specify step to run. Choices: migrations. You can add this option multiple times. If not specified all steps will be executed.'
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->findExecutable();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $steps = $input->getOption('step');
        $io = new SymfonyStyle($input, $output);

        if (\in_array('tests', $steps) || empty($steps)) {
            $io->section('Test environment');

            $this->solrClient->execute(new Query());
            $io->success('Solr connection successful');

            $bundleErrors = $this->bundleTest->execute();
            if (\count($bundleErrors) > 0) {
                foreach ($bundleErrors as $bundleError) {
                    $io->error($bundleError);
                }
            } else {
                $io->success('Bundle test successful');
            }
        }

        if (\in_array('cache', $steps) || empty($steps)) {
            $io->section('Clear cache');

            $this->executeCommand('cache:clear', $output);
        }

        if (\in_array('assets', $steps) || empty($steps)) {
            $io->section('Install assets');

            $this->executeCommand('assets:install', $output);
        }

        if (\in_array('migrations', $steps) || empty($steps)) {
            $io->section('Execute migrations');

            $this->executeCommand('integrated:install:database:migrate --no-interaction', $output);
            $this->executeCommand('integrated:install:mongodb:migrate', $output);
        }

        return self::SUCCESS;
    }

    private function executeCommand($command, OutputInterface $output): void
    {
        $command = implode(' ', [$this->php, 'bin/console', $command, '-e', $this->kernel->getEnvironment()]);

        $output->writeln(sprintf('Execute %s', $command), OutputInterface::VERBOSITY_VERY_VERBOSE);

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->run(function ($type, $buffer) use ($output): void {
            if (Process::ERR === $type) {
                $output->write($buffer, false, $output::OUTPUT_RAW | $output::VERBOSITY_NORMAL);
            } else {
                $output->write($buffer, false, $output::OUTPUT_RAW | $output::VERBOSITY_VERBOSE);
            }
        });

        if (!$process->isSuccessful()) {
            $output->writeln(sprintf('Command %s failed', $command));
        }
    }

    private function findExecutable(): void
    {
        $finder = new PhpExecutableFinder();

        if (!$path = $finder->find(false)) {
            throw new \RuntimeException(
                'The php executable could not be found, add it to your PATH environment variable and try again'
            );
        }

        $this->php = $path;
    }
}
