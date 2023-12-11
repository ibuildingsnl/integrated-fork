<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ODM\MongoDB\Query\Builder;
use function Deployer\output;
use function Deployer\writeln;


class GetMostReadDatasCommand extends Command
{
    private OutputInterface $output;
    private string $dataType = "most_read";

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
            ->setName('most:read')
            ->setDescription('Get "most read" datas');
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
        foreach ($channels as $channel)
        {
            $this->output->writeln('- Getting '.$channel->getName().'\'s Most read datas');
            $allDatas = $this->getData($channel, $analyticsRequest);
            $analyticsRequest->setDataToDB($channel, $this->dataType, $allDatas);
        }
        return 1;
    }

    public function getChannels(): array
    {
        $channels = [];
        foreach ($this->channelRepository->findAll() as $channel) {
            if ($channel->getPrimaryDomain() != null) {
                $channels[] = $channel;
            }
        }
        return $channels;
    }

    function getViewsBySlug($slug, $data)
    {
        foreach ($data as $entry) {
            if ($entry['slug'] === $slug) {
                return $entry['views'];
            }
        }
        return null;
    }

    public function sendDataFromAnalyticsToDb(AnalyticsRequest $analyticsRequest, $channel): void
    {
        $pagesDatas = [];
        $allDatas = $this->getData($analyticsRequest, $channel);
        if ($allDatas == []) {return;}

        $weeklyDatas = $allDatas['weeklyMostRead'];
        $monthlyDatas = $allDatas['monthlyMostRead'];
        $quarterlyDatas = $allDatas['quarterlyMostRead'];
        $semesterDatas = $allDatas['semesterMostRead'];
        $yearlyDatas = $allDatas['yearlyMostRead'];

        foreach ($yearlyDatas as $entry) {
            $slug = $entry['slug'];
            $pagesDatas[] = [
                'title' => $entry['title'],
                'slug' => $slug,
                'weeklyViews' => $this->getViewsBySlug($slug, $weeklyDatas),
                'monthlyViews' => $this->getViewsBySlug($slug, $monthlyDatas),
                'quarterlyViews' => $this->getViewsBySlug($slug, $quarterlyDatas),
                'semesterViews' => $this->getViewsBySlug($slug, $semesterDatas),
                'yearlyViews' => $entry['views'] ?? null
            ];
        }

        try {
            foreach ($pagesDatas as $page) {
                $article = $this->manager
                    ->getRepository(Article::class)
                    ->findOneBy([
                        'slug' => $page['slug'],
                        'channels.id' => $channel->getId(),
                    ]);
                if ($page['slug'] == "" or $article == null) {
                    continue;
                }
                $metadata = $article->getMetadata();
                $metadata->set('weeklyViews', $page['weeklyViews']);
                $metadata->set('monthlyViews', $page['monthlyViews']);
                $metadata->set('quarterlyViews', $page['quarterlyViews']);
                $metadata->set('semesterViews', $page['semesterViews']);
                $metadata->set('yearlyViews', $page['yearlyViews']);
                $this->output->writeln($page['slug']);
            }
            $this->manager->flush();
        } catch (\Throwable $throwable) {
            $this->logger->error('Get Most Read Error: ' . $throwable->getMessage());
        }
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
                "dateRanges" => [
                    [
                        "startDate" => $dateRange,
                        "endDate" => "today"
                    ]
                ],
                "dimensions" => [
                    [
                        "name" => "pageTitle"
                    ],
                    [
                        "name" => "fullPageUrl"
                    ]
                ],
                "metrics" => [
                    [
                        "name" => "screenPageViews"
                    ]
                ],
            ];
            $responseData = $analyticsRequest->getDataFromAnalytics($channel, $requestBody);
            $mostViewedPages = [];
            if ($responseData == null) {
                $message = "Get Most Read Error: No datas found for" . $channel->getName() . "in date range: $dateRange \n";
                $this->logger->error($message);
                $this->output->writeln($message);
            } else {
                foreach ($responseData['rows'] as $row) {
                    $pageTitle = $row['dimensionValues'][0]['value'];
                    $fullPageUrl = $row['dimensionValues'][1]['value'];
                    $screenPageViews = (int)$row['metricValues'][0]['value'];
                    $mostViewedPages[] = [
                        'title' => $pageTitle,
                        'slug' => substr(strrchr($fullPageUrl, "/"), 1),
                        'views' => $screenPageViews,
                    ];
                }
                $allDatas[$key] = $mostViewedPages;
            }
        }
        return $allDatas;
    }
}
