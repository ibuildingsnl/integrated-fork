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

class DeviceTypeWidget implements WidgetInterface
{

    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
        private readonly string          $credential,
        private readonly LoggerInterface  $logger,
        private readonly BrandRepository  $brandRepository,
    )
    {
        $this->id = 'device_type';
        $this->name = 'Device type';
        $this->view = '@IntegratedDashboard/device_type.html.twig';
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
        //$deviceType = $this->getDataFromAnalytics($propertyId, $channel, '365daysAgo');


        $dateRanges = [
            'weeklyDeviceType' => '7daysAgo',
            'monthlyDeviceType' => '30daysAgo',
            'quarterlyDeviceType' => '90daysAgo',
            'semesterDeviceType' => '182daysAgo',
            'yearlyDeviceType' => '365daysAgo',
        ];

        $allDatas = [];
        foreach ($dateRanges as $key => $dateRange) {
            $allDatas[$key] = $this->getDataFromAnalytics($propertyId, $channel, $dateRange);
        }

        //dd($allDatas);
        return [
            "widget" => $this,
            "deviceType" => $allDatas,
        ];
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
                $message = "Get Most Read Error: No datas found for" . $channel->getName() . "in date range: $dateRange \n";
                $this->logger->error($message);
                //$this->output->writeln($message);
            }
        }
        return $deviceType;
    }
}
