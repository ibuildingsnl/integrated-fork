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


//        $websites = [
//            ['id' => 'vakbladijs', 'domain' => 'https://www.vakbladijs.nl'],
//            ['id' => 'vismagazine', 'domain' => 'https://www.vismagazine.nl'],
//            ['id' => 'vleesmagazine', 'domain' => 'https://www.vleesmagazine.nl'],
//            ['id' => 'evmi', 'domain' => 'https://www.evmi.nl'],
//            ['id' => 'beveragenl', 'domain' => 'https://www.morethandrinks.nl'],
//            ['id' => 'voedingnu', 'domain' => 'https://www.voedingnu.nl'],
//            ['id' => 'automationnl', 'domain' => 'https://www.automationnl.nl'],
//        ];

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
        foreach ($websites as $website) {
            $url = $website['domain'];
            if ((filter_var($url, FILTER_VALIDATE_URL) === false) || (str_contains($url, 'localhost'))) {
                continue;
            }
            $encodedUrl = urlencode($url);
            try {
                $request = "https://pagespeedonline.googleapis.com/pagespeedonline/v5/runPagespeed?url=$encodedUrl&category=PERFORMANCE";
                $response = $this->client->get($request);
                if ($response->getStatusCode() === 200) {
                    $content = $response->getBody()->getContents();
                    $data = json_decode($content, true);
                    $speedIndex = $data['lighthouseResult']['audits']['speed-index']['numericValue'] ?? null;

                    if (!is_null($speedIndex)) {
                        $dateTime = new \DateTimeImmutable();
                        $sitePerformance = new SitePerformance($website['id'], $speedIndex, $dateTime);
                        $output->writeln($sitePerformance->getSiteSpeed());
                        $this->manager->persist($sitePerformance);
                    }
                } else {
                    $dateTime = new \DateTimeImmutable();
                    $errorMessage = 'Status Code:' . $response->getStatusCode() . '|' . $response->getHeaderLine() . '\n' . $response->getBody(). '\n' . $dateTime;
                    $this->logger->error('Get Site Performance Error: ' . $errorMessage);
                }
            } catch (\InvalidArgumentException $e) {
                $dateTime = new \DateTimeImmutable();
                $this->logger->error('Get Site Performance Error: ' . $e->getMessage(). '\n' . $dateTime);
            } catch (GuzzleException $e) {
                $dateTime = new \DateTimeImmutable();
                $this->logger->error('Get Site Performance Error: ' . $e->getMessage(). '\n' . $dateTime);
            }
        }
        $this->manager->flush();

        return 0;
    }
}
