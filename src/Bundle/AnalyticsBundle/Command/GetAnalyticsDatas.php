<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\MongoDBException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Finder\Finder;


class GetAnalyticsDatas extends Command
{
    private OutputInterface $output;
    private ContainerInterface $container;

    /**
     * Constructor.
     */
    public function __construct(
        private readonly LoggerInterface  $logger,
        ContainerInterface $container,
    )
    {
        parent::__construct();
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('analytics:datas')
            ->setDescription('Get all Analytics datas');
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $finder = new Finder();
        $finder->files()->in(__DIR__)->name('*Command.php');
        $commandsToRun = [];

        foreach ($finder as $file) {
            $className = 'Integrated\Bundle\AnalyticsBundle\Command\\' . $file->getBasename('.php');
            if ($className != GetAnalyticsDatas::class) {
                $commandsToRun[] = $className;
            }
        }
        foreach ($commandsToRun as $commandClass) {
            try {
                $this->output->writeln("<info> Running '$commandClass' command\n</info>");
                $command = $this->container->get($commandClass);
                $commandInput = new ArrayInput([]);
                $command->run($commandInput, $output);
            } catch (\Exception $e) {
                $output->writeln("<error>Error executing command '$commandClass': " . $e->getMessage()."</error>");
            }
            $this->output->writeln("\n\n");
        }

        return Command::SUCCESS;
    }
}
