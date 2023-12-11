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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTimeImmutable;


class GetTrafficAcquisitionCommand extends Command
{
    private OutputInterface $output;
    private string $dataType = "traffic_acquisition";

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
            ->setName('traffic:acquisition')
            ->setDescription('Get "Trafic Acquisition" datas');
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
            $this->output->writeln('- Getting '.$channel->getName().'\'s Trafic Acquisition  datas');
            $propertyId = $this->getPropertyID($channel);
            $allDatas = $this->getData($channel, $propertyId);
            $this->setDataToDB($channel, $this->dataType, $allDatas);
        }

        return 1;
    }
    public function getPropertyID(ChannelInterface $channel): ?string
    {
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            $this->logger->error($channel->getName(). ' Error: no property ID found');
            return null;
        }
        return $propertyId;
    }

    public function getData(ChannelInterface $channel, $propertyId): array
    {
        $dateRanges = [
            'weeklyTrafficAcquisition' => '7daysAgo',
            'monthlyTrafficAcquisition' => '30daysAgo',
            'quarterlyTrafficAcquisition' => '90daysAgo',
            'semesterTrafficAcquisition' => '182daysAgo',
            'yearlyTrafficAcquisition' => '365daysAgo',
        ];

        $allDatas = [];
        foreach ($dateRanges as $key => $dateRange) {
            $allDatas[$key] = $this->getDataFromAnalytics($propertyId, $channel, $dateRange);
        }
        return $allDatas;
    }
    public function setDataToDB(ChannelInterface $channel, $dataType ,$allDatas): void
    {
        $trafficAcquisition = new AnalyticsData($channel->getId(), $dataType, $allDatas, new DateTimeImmutable());
        $this->manager->persist($trafficAcquisition);
        $this->manager->flush();
    }

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
                        "name" => "sessionDefaultChannelGroup"
                    ],
                ],
                "metrics" => [
                    [
                        "name" => "sessions"
                    ]
                ],

            ];
            $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
            $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
            $responseData = $analyticsRequest->getResponse();
            $trafficAcquisition = [];
            if ($responseData != null and isset($responseData['rows'])) {
                foreach ($responseData['rows'] as $row) {
                    $source = $row['dimensionValues'][0]['value'];
                    $sessions = (int)$row['metricValues'][0]['value'];
                    $trafficAcquisition[] = [
                        'source' => $source,
                        'sessions' => $sessions,
                    ];
                }
            } else {
                $message = "Get Most Read Error: No datas found for" . $channel->getName() . "in date range: $dateRange \n";
                $this->logger->error($message);
                $this->output->writeln($message);
            }
        }
        return $trafficAcquisition;
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
