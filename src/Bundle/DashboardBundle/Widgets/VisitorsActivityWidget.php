<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\DashboardBundle\Widgets\WidgetInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;

class VisitorsActivityWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly string          $credential,
        private readonly LoggerInterface $logger,
        private readonly BrandRepository $brandRepository
    ){
        $this->id = 'visitors_activity';
        $this->name = 'Visitors activity';
        $this->view = '@IntegratedDashboard/visitors_activity.html.twig';
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function view(): string
    {
        return $this->view;
    }

    /**
     * @throws GuzzleException
     */
    public function params(ChannelInterface $channel, User $user, Request $request): array
    {
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            return ["VisitorActivity" => "No data found"];
        }
        $userActivityByDate = $this->getDataFromAnalytics($propertyId);
        return [
            "widget" => $this,
            "userActivityByDate" => $userActivityByDate,
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function getDataFromAnalytics(string $propertyId): array
    {
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
            "metricAggregations" => [
                "TOTAL"
            ]
        ];

        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
        $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
        $responseData = $analyticsRequest->getResponse();
        $userActivityByDate = [];
        if ($responseData != null) {
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
        }
        return $userActivityByDate;
    }
}
