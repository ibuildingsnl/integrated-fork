<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class TrafficAcquisitionWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly string          $credential,
        private readonly LoggerInterface $logger,
        private readonly BrandRepository $brandRepository,
    )
    {
        $this->id = 'traffic_acquisition';
        $this->name = 'Traffic acquisition';
        $this->view = '@IntegratedDashboard/traffic_acquisition.html.twig';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getParams(ChannelInterface $channel, User $user, Request $request): array
    {
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            return ["DeviceType" => "No data found"];
        }

        $dateRanges = [
            'weeklyTrafficAcquisition' => '7daysAgo',
            'monthlyTrafficAcquisition' => '30daysAgo',
            'quarterlyTrafficAcquisition' => '90daysAgo',
            'semesterTrafficAcquisition' => '182daysAgo',
            'yearlyTrafficAcquisition' => '365daysAgo',
        ];

        $allDatas = [];
        $maxElements = 9;
        foreach ($dateRanges as $key => $dateRange) {
            $allDatas[$key] = $this->getDataFromAnalytics($propertyId, $channel, $dateRange);
            if (count($allDatas[$key]) > $maxElements)
            {
                $allDatas[$key] = $this->processTrafficAcquisition($allDatas[$key], $maxElements);
            }
        }
        return [
            "widget" => $this,
            "trafficAcquisition" => $allDatas,
        ];
    }

    function processTrafficAcquisition(array $trafficAcquisition, $maxElements): array
    {
        $otherSessions = 0;

        // Calcul du cumul des sessions et stockage des sources à partir du 10ème élément
        for ($i = $maxElements; $i < count($trafficAcquisition); $i++) {
            $otherSessions += $trafficAcquisition[$i]['sessions'];
        }

        // Suppression des éléments à partir du 10ème
        array_splice($trafficAcquisition, $maxElements);

        // Ajout de l'élément "Other" avec le cumul des sessions
        $trafficAcquisition[] = [
            'source' => 'Other',
            'sessions' => $otherSessions
        ];

        return $trafficAcquisition;
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
                //$this->output->writeln($message);
            }
        }
        return $trafficAcquisition;
    }
}
