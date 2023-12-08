<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use DateTimeImmutable;



class GetDeviceTypeCommand extends Command
{
    private OutputInterface $output;
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
            ->setName('device:type')
            ->setDescription('Get "Device type" datas');
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $channels = $this->getChannels();
        foreach ($channels as $channel)
        {
            $this->output->writeln('- Getting '.$channel->getName().'\'s Device Type datas');
            $this->setDataToDB($channel);
        }

        $this->manager->flush();
        return 1;
    }
    public function setDataToDB(ChannelInterface $channel): void
    {
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            $this->logger->error('Get Device Type Error: no property ID found');
            return;
        }

        $dateRanges = [
            'weeklyDeviceType' => '7daysAgo',
            'monthlyDeviceType' => '30daysAgo',
            'quarterlyDeviceType' => '90daysAgo',
            'semesterDeviceType' => '182daysAgo',
            'yearlyDeviceType' => '365daysAgo',
        ];

        $allDatas = [];
        foreach ($dateRanges as $key => $dateRange) {
            $allDatas[$key] = $this->getDataFromAnalytics($propertyId,$channel, $dateRange);
        }

        $deviceType = new AnalyticsData($channel->getId(), 'device_type', $allDatas, new DateTimeImmutable());
        $this->manager->persist($deviceType);
    }

    /**
     * @throws GuzzleException
     */
    public function getDataFromAnalytics(string $propertyId, $channel, string $dateRange): array
    {
        {
            $requestBody = [
                "dateRanges" => [
                    [
                        "startDate" => $dateRange,
                        "endDate" => "today"
                    ]
                ],
                "dimensions" => [
                    [
                        "name" => "deviceCategory"
                    ],
                ],
                "metrics" => [
                    [
                        "name" => "screenPageViews"
                    ]
                ],
            ];
            $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
            $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
            $responseData = $analyticsRequest->getResponse();
            $deviceType = [];
            if ($responseData != null and isset($responseData['rows'])) {
                foreach ($responseData['rows'] as $row) {
                    $deviceCategory = $row['dimensionValues'][0]['value'];
                    $screenPageViews = (int)$row['metricValues'][0]['value'];
                    $deviceType[] = [
                        'device' => $deviceCategory,
                        'amount' => $screenPageViews,
                    ];
                }
            } else {
                $message = "Get Device Type Error: No datas found for" . $channel->getName() . "in date range: $dateRange \n";
                $this->logger->error($message);
                $this->output->writeln($message);
            }
        }
        return $deviceType;
    }

    public function getChannels(): array
    {
        $channels = [];
        foreach ($this->channelRepository->findAll() as $channel) {
            if ($channel->getPrimaryDomain() != null)
            {
                $channels[] = $channel;
            }
        }
        return $channels;
    }
}
