<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetMostReadDatasCommand extends Command
{
    private OutputInterface $output;
    private string $dataType = 'most_read';

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
            ->setName('most:read')
            ->setDescription('Get "most read" datas');
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
            $this->output->writeln('- Getting '.$channel->getName().'\'s Most read datas');
            $allDatas = $this->getData($channel, $analyticsRequest);
            $analyticsRequest->setDataToDB($channel, $this->dataType, $allDatas);
        }

        return 1;
    }

    /**
     * @throws GuzzleException
     */
    public function getData($channel, $analyticsRequest): array
    {
        $dateRanges = [
            'weeklyMostReadArticles' => '7daysAgo',
            'monthlyMostReadArticles' => '30daysAgo',
            'quarterlyMostReadArticles' => '90daysAgo',
            'semesterMostReadArticles' => '182daysAgo',
            'yearlyMostReadArticles' => '365daysAgo',
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
                        'name' => 'pageTitle',
                    ],
                    [
                        'name' => 'fullPageUrl',
                    ],
                ],
                'metrics' => [
                    [
                        'name' => 'screenPageViews',
                    ],
                ],
            ];
            $responseData = $analyticsRequest->getDataFromAnalytics($channel, $requestBody);
            $mostViewedPages = [];
            if ($responseData == null) {
                $message = 'Get Most Read Error: No datas found for'.$channel->getName()."in date range: $dateRange \n";
                $this->logger->error($message);
                $this->output->writeln($message);
            } else {
                foreach ($responseData['rows'] as $row) {
                    $pageTitle = $row['dimensionValues'][0]['value'];
                    $fullPageUrl = $row['dimensionValues'][1]['value'];
                    $screenPageViews = (int) $row['metricValues'][0]['value'];
                    $mostViewedPages[] = [
                        'title' => $pageTitle,
                        'slug' => substr(strrchr($fullPageUrl, '/'), 1),
                        'views' => $screenPageViews,
                    ];
                }
                $allDatas[$key] = $mostViewedPages;
            }
        }

        return $allDatas;
    }
}
