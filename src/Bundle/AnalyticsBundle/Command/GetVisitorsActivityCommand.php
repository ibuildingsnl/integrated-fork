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

class GetVisitorsActivityCommand extends Command
{
    private OutputInterface $output;
    private string $dataType = "visitors_activity";

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
            ->setName('visitors:activity')
            ->setDescription('Get "Visitors activity" datas');
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger, $this->brandRepository, $this->channelRepository, $this->manager);
        $channels = $analyticsRequest->getChannels();

        foreach ($channels as $channel) {
            $this->output->writeln('- Getting ' . $channel->getName() . '\'s Visitors activity  datas');
            $allDatas = $this->getData($channel, $analyticsRequest);
            $analyticsRequest->setDataToDB($channel, $this->dataType, $allDatas);
        }
        return 1;
    }

    public function getData(ChannelInterface $channel, $analyticsRequest): ?array
    {
        $analyticsRequest->getPropertyID($channel);
        $requestBody = [
            "dimensions" => [
                [
                    "name" => "date"
                ],
            ],
            "metrics" => [
                [
                    "name" => "activeUsers"
                ],
                [
                    "name" => "bounceRate"
                ],
                [
                    "name" => "screenPageViews"
                ],
            ],
            "dateRanges" => [
                [
                    "startDate" => '365daysAgo',
                    "endDate" => "today"
                ]
            ],
            "orderBys" =>
                [
                    "dimension" => [
                        "orderType" => "NUMERIC",
                        "dimensionName" => "date"
                    ],
                    "desc" => false,
                ],
            "metricAggregations" => [
                "TOTAL"
            ]
        ];

        $responseData = $analyticsRequest->getDataFromAnalytics($channel, $requestBody);
        if ($responseData == null) {
            $message = "Get Visitors Activity Error: No datas found for" . $channel->getName() ."\n";
            $this->logger->error($message);
            $this->output->writeln($message);
            return null;
        }
        $userActivityByDate = [];
        foreach ($responseData['rows'] as $row) {
            $date = $row['dimensionValues'][0]['value'];
            $activeUsers = $row['metricValues'][0]['value'];
            $bounceRate = $row['metricValues'][1]['value'];
            $screenPageViews = $row['metricValues'][2]['value'];

            $userActivityByDate[] = [
                'date' => $date,
                'userCount' => $activeUsers,
                'bounceRate' => round($bounceRate * 100,2),
                'screenPageViews' => $screenPageViews,
            ];
        }
        $userActivityByDate = array_reverse($userActivityByDate);
        $allDatas = [
            'visitorsActivity' => $userActivityByDate,
        ];

        return $allDatas;
    }
}
