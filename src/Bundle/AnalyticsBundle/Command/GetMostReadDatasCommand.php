<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
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
        $channels = $this->getChannels();
        foreach ($channels as $channel) {
            $this->output->writeln('- Getting '.$channel->getName().'\'s Most read datas');
            foreach ($this->brandRepository->all() as $brand) {
                if ($brand->hasChannel($channel)) {
                    $propertyId = $brand->profile->analytics;
                }
            }
            if (!isset($propertyId))
            {
                $this->logger->error('Get Most read Error: no property ID found');
                return 0;
            }
            $this->sendDataFromAnalyticsToDb($propertyId, $channel);
        }
        return 0;
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

    function getViewsBySlug($slug, $data)
    {
        foreach ($data as $entry) {
            if ($entry['slug'] === $slug) {
                return $entry['views'];
            }
        }
        return null;
    }

    public function sendDataFromAnalyticsToDb(string $propertyId, $channel): void
    {
        $pagesDatas = [];

        $weeklyDatas = $this->getDataFromAnalytics($propertyId, $channel, "7daysAgo");
        $monthlyDatas = $this->getDataFromAnalytics($propertyId, $channel, "3daysAgo");
        $quarterlyDatas = $this->getDataFromAnalytics($propertyId, $channel, "90daysAgo");
        $semesterDatas = $this->getDataFromAnalytics($propertyId, $channel, "182daysAgo");
        $yearlyDatas = $this->getDataFromAnalytics($propertyId, $channel, "365daysAgo");


        foreach ($weeklyDatas as $entry) {
            $slug = $entry['slug'];
            $pagesDatas[] = [
                'title' => $entry['title'],
                'slug' => $slug,
                'weeklyViews' => $entry['views'] ?? null,
                'monthlyViews' => $this->getViewsBySlug($slug, $monthlyDatas),
                'quarterlyViews' => $this->getViewsBySlug($slug, $quarterlyDatas),
                'semesterViews' => $this->getViewsBySlug($slug, $semesterDatas),
                'yearlyViews' => $this->getViewsBySlug($slug, $yearlyDatas),
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
            }
            $this->manager->flush();
        } catch (\Throwable $throwable) {
            $this->logger->error('Get Most Read Error: ' . $throwable->getMessage());
        }
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
            $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
            $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
            $responseData = $analyticsRequest->getResponse();
            $mostViewedPages = [];
            if ($responseData != null and isset($responseData['rows'])) {
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
            } else {
                $message = "Get Most Read Error: No datas found for" . $channel->getName() . "in date range: $dateRange \n";
                $this->logger->error($message);
                $this->output->writeln($message);
            }
        }
        return $mostViewedPages;
    }

}
