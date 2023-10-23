<?php

namespace Integrated\Bundle\AnalyticsBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Client;
use Integrated\Bundle\AnalyticsBundle\Document\SitePerformance;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Exception\GuzzleException;
use function Deployer\writeln;


class GetSitePerformancesCommand extends Command
{
    /**
     * Constructor.
     */
    public function __construct(
        private readonly DocumentManager  $manager,
        private readonly ObjectRepository $channelRepository,
        private readonly Client           $client,
        private readonly LoggerInterface $logger,
    )
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('site:performance')
            ->setDescription('Get site performance');
    }

    /**
     * {@inheritdoc}
     * @throws MongoDBException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $websites = $this->getWebsites();

        foreach ($websites as $website) {
            $url = $website['domain'];
            if (!$this->isValidUrl($url)) {
                continue;
            }
            $encodedUrl = urlencode($url);
            try {
                $this->getSitePerformance($encodedUrl, $website['id']);
                $this->saveSitePerformance();
            } catch (\InvalidArgumentException $e) {
                $dateTime = new \DateTimeImmutable();
                $this->logger->error('Get Site Performance Error: ' . $e->getMessage(). '\n' . $dateTime);
            }
        }

        return 0;
    }
    public function getWebsites(): array
    {
        $websites = [];
        /** @var Channel $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            $domain = $channel->getPrimaryDomain();
            if ($domain === null) {
                continue;
            }
            $websites[] = [
                'id' => $channel->getId(),
                'domain' => 'https://www.' . $domain
            ];
        }
        return $websites;
    }

    public function isValidUrl(string $url): bool
    {
        return !((filter_var($url, FILTER_VALIDATE_URL) === false) || (str_contains($url, 'localhost')));
    }

    public function getSitePerformance(string $url, string $channelId): void
    {
        try{
            $ApiKey = 'AIzaSyCy9x4Iu2dvAJo6MVpSu9x-LNKQOF-7p9c';
            $request = "https://pagespeedonline.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&category=PERFORMANCE&key=$ApiKey";
            $response = $this->client->get($request);
            if ($response->getStatusCode() === 200) {
                $content = $response->getBody()->getContents();
                $data = json_decode($content, true);
                $speedIndex = $data['lighthouseResult']['audits']['speed-index']['numericValue'] ?? null;
                if (!is_null($speedIndex)) {
                    $dateTime = new \DateTimeImmutable();
                    $sitePerformance = new SitePerformance($url, $speedIndex, $dateTime);
                    $this->manager->persist($sitePerformance);
                }
            } else {
                $dateTime = new \DateTimeImmutable();
                $errorMessage = 'Status Code:' . $response->getStatusCode() . '|' . $response->getHeaderLine() . '\n' . $response->getBody(). '\n' . $dateTime;
                $this->logger->error('Get Site Performance Error: ' . $errorMessage);
            }
        }
        catch (GuzzleException $e) {
            $dateTime = new \DateTimeImmutable();
            $this->logger->error('Get Site Performance Error: ' . $e->getMessage(). '\n' . $dateTime);
        }

    }

    /**
     * @throws MongoDBException
     */
    public function saveSitePerformance(): void
    {
        $this->manager->flush();
    }
}
