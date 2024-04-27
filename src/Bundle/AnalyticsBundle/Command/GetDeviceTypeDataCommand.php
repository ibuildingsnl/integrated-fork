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

class GetDeviceTypeDataCommand extends Command
{
    private OutputInterface $output;
    private string $dataType = 'device_type';

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
            ->setName('device:type')
            ->setDescription('Get "Device type" data');
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
            $this->output->writeln('- Getting '.$channel->getName().'\'s Device Type  data');
            $allData = $this->getData($channel, $analyticsRequest);
            $analyticsRequest->setDataToDB($channel, $this->dataType, $allData);
        }

        return 1;
    }

    public function getData(ChannelInterface $channel, $analyticsRequest): array
    {
        $dateRanges = [
            'weeklyDeviceType' => '7daysAgo',
            'monthlyDeviceType' => '30daysAgo',
            'quarterlyDeviceType' => '90daysAgo',
            'semesterDeviceType' => '182daysAgo',
            'yearlyDeviceType' => '365daysAgo',
        ];

        $allData = [];
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
                        'name' => 'deviceCategory',
                    ],
                ],
                'metrics' => [
                    [
                        'name' => 'screenPageViews',
                    ],
                ],
            ];

            $responseData = $analyticsRequest->getDataFromAnalytics($channel, $requestBody);
            if ($responseData == null) {
                $message = 'Get Device Type Error: No data found for '.$channel->getName().' in date range: $dateRange \n';
                $this->logger->error($message);
                $this->output->writeln($message);
                continue;
            }
            $deviceType = [];
            foreach ($responseData['rows'] as $row) {
                $deviceCategory = $row['dimensionValues'][0]['value'];
                $screenPageViews = (int) $row['metricValues'][0]['value'];
                $deviceType[] = [
                    'device' => $deviceCategory,
                    'amount' => $screenPageViews,
                ];
            }
            $allData[$key] = $deviceType;
        }

        return $allData;
    }
}
