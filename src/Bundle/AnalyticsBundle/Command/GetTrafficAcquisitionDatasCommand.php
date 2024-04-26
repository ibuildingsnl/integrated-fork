<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetTrafficAcquisitionDatasCommand extends Command
{
    private OutputInterface $output;
    private string $dataType = 'traffic_acquisition';

    /**
     * Constructor.
     */
    public function __construct(
        private readonly string $credential,
        private readonly DocumentManager $manager,
        private readonly ObjectRepository $channelRepository,
        private readonly BrandRepository $brandRepository,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('traffic:acquisition')
            ->setDescription('Get "Trafic Acquisition" datas');
    }

    /**
     * {@inheritdoc}
     *
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger, $this->brandRepository, $this->channelRepository, $this->manager);
        $channels = $analyticsRequest->getChannels();
        foreach ($channels as $channel) {
            $this->output->writeln('- Getting '.$channel->getName().'\'s Trafic Acquisition  datas');
            $allDatas = $this->getData($channel, $analyticsRequest);
            $analyticsRequest->setDataToDB($channel, $this->dataType, $allDatas);
        }

        return 1;
    }

    public function getData(ChannelInterface $channel, $analyticsRequest): array
    {
        $analyticsRequest->getPropertyID($channel);
        $dateRanges = [
            'weeklyTrafficAcquisition' => '7daysAgo',
            'monthlyTrafficAcquisition' => '30daysAgo',
            'quarterlyTrafficAcquisition' => '90daysAgo',
            'semesterTrafficAcquisition' => '182daysAgo',
            'yearlyTrafficAcquisition' => '365daysAgo',
        ];

        $allDatas = [];
        foreach ($dateRanges as $key => $dateRange) {
            $requestBody = [
                'dateRanges' => [
                    [
                        'startDate' => $dateRange,
                        'endDate' => 'today',
                    ],
                ],
                'dimensions' => [
                    [
                        'name' => 'sessionDefaultChannelGroup',
                    ],
                ],
                'metrics' => [
                    [
                        'name' => 'sessions',
                    ],
                ],
            ];
            $responseData = $analyticsRequest->getDataFromAnalytics($channel, $requestBody);
            if ($responseData == null) {
                $message = 'Get Most Read Error: No datas found for'.$channel->getName()."in date range: $dateRange \n";
                $this->logger->error($message);
                $this->output->writeln($message);
                continue;
            }
            $trafficAcquisition = [];
            foreach ($responseData['rows'] as $row) {
                $source = $row['dimensionValues'][0]['value'];
                $sessions = (int) $row['metricValues'][0]['value'];
                $trafficAcquisition[] = [
                    'source' => $source,
                    'sessions' => $sessions,
                ];
            }
            $allDatas[$key] = $trafficAcquisition;
        }

        return $allDatas;
    }
}
