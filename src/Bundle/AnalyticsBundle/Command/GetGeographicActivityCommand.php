<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTimeImmutable;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;

class GetGeographicActivityCommand extends Command
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
            ->setName('geographic:activity')
            ->setDescription('Get "Geographic activity" datas');
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
            $this->output->writeln('- Getting '.$channel->getName().'\'s Geographic activity datas');
            $this->setDataToDB($channel);
        }

        $this->manager->flush();
        return 1;
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

    /**
     * @throws GuzzleException
     */
    public function getDataFromAnalytics(string $propertyId, $channel, string $dateRange): array
    {
        $requestBody = [
            "dateRanges" => [
                [
                    "startDate" => "$dateRange",
                    "endDate" => "today"
                ]
            ],
            "dimensions" => [
                [
                    "name" => "country"
                ],
                [
                    "name" => "city"
                ],
            ],
            "metrics" => [
                [
                    "name" => "bounceRate"
                ],
                [
                    "name" => "totalUsers"
                ]
            ],
            "orderBys" => [
                [
                    "metric" => [
                        "metricName" => "totalUsers"
                    ],
                    "desc" => true
                ]
            ],
            "metricAggregations" => [
                "TOTAL"
            ]
        ];
        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger, $this->brandRepository, $this->channelRepository, $this->manager);
        $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
        $responseData = $analyticsRequest->getResponse();
        $allDatas = [];
        if ($responseData != null and isset($responseData['rows'])) {
            $GeographicActivity = $this->getGeographicActivity($responseData);
            $siteTotals = $this->getSiteTotals($responseData);
            $allDatas = [
                'GeographicActivity' => $GeographicActivity,
                'siteTotals' => $siteTotals,
            ];
        } else {
            $message = "Get Geographic Activity Error: No datas found for" . $channel->getName() . "in date range: $dateRange \n";
            $this->logger->error($message);
            $this->output->writeln($message);
        }
        return $allDatas;
    }

    public function setDataToDB(ChannelInterface $channel): void
    {
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            $this->logger->error('Get Geographic activity Error: no property ID found');
            return;
        }

        $dateRanges = [
            'weeklyGeographicActivity' => '7daysAgo',
            'monthlyGeographicActivity' => '30daysAgo',
            'quarterlyGeographicActivity' => '90daysAgo',
            'semesterGeographicActivity' => '182daysAgo',
            'yearlyGeographicActivity' => '365daysAgo',
        ];

        $allDatas = [];
        foreach ($dateRanges as $key => $dateRange) {
            $allDatas[$key] = $this->getDataFromAnalytics($propertyId, $channel, $dateRange);
        }

        $geographicActivity = new AnalyticsData($channel->getId(), 'geographic_activity', $allDatas, new DateTimeImmutable());
        $this->manager->persist($geographicActivity);
    }

    private function getGeographicActivity(array $responseData): array
    {
        $GeographicActivity = [];
        foreach ($responseData['rows'] as $row) {
            $country = $row['dimensionValues'][0]['value'];
            $city = $row['dimensionValues'][1]['value'];
            $bounceRate = (int)$row['metricValues'][0]['value'];
            $totalUser = (int)$row['metricValues'][1]['value'];
            $GeographicActivity[] = [
                'country' => $country,
                'city' => $city,
                'bounceRate' => round($bounceRate * 100, 2),
                'totalUser' => $totalUser,
            ];
        }
        return $GeographicActivity;
    }

    private function getSiteTotals(array $responseData): array
    {
        $totalsData = $responseData['totals'][0];
        return [
            'bounceRate' => round(($totalsData['metricValues'][0]['value'] * 100), 2),
            'totalUser' => $totalsData['metricValues'][1]['value'],
        ];
    }


}
