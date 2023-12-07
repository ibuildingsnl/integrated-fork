<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteAnalyticsCommandsCommand extends Command
{
    /**
     * Constructor.
     */
    public function __construct(
        private readonly string           $credential,
        private readonly DocumentManager  $manager,
        private readonly ObjectRepository $channelRepository,
        private readonly BrandRepository  $brandRepository,
        private readonly LoggerInterface  $logger,
    )
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('execute:analytics')
            ->setDescription('execute all the analytics commands');
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $getGeographicActivityCommand = new GetGeographicActivityCommand($this->credential,$this->manager, $this->channelRepository, $this->brandRepository, $this->logger);
        $getGeographicActivityCommand->execute($input, $output);

        $getMostReadDatasCommand = new GetMostReadDatasCommand($this->credential,$this->manager, $this->channelRepository, $this->brandRepository, $this->logger);
        $getMostReadDatasCommand->execute($input, $output);

        $getGeographicActivityCommand = new GetSitePerformancesCommand($this->manager, $this->channelRepository, $this->brandRepository, $this->logger);
        $getGeographicActivityCommand->execute($input, $output);

        return 0;
    }
}
