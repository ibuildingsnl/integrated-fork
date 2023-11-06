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
use DateTimeImmutable;

class VisitorsActivityWidget implements WidgetInterface
{

    public function __construct(
        private readonly string          $credential,
        private readonly LoggerInterface $logger,
        private readonly DocumentManager $manager,
        private readonly BrandRepository $brandRepository
    )
    {
    }

    public function id(): string
    {
        return 'visitors_activity';
    }

    public function name(): string
    {
        return 'Visitors activity';
    }

    public function view(): string
    {
        return '@IntegratedDashboard/visitors_activity.html.twig';
    }

    public function params(ChannelInterface $channel, User $user, Request $request): array
    {
        $dateRange = $request->query->get('visitors_activity_date_range') ?? "7daysAgo";
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            return ["mostViewedPages" => "No data found"];
        }
        $requestBody = [
            "dateRanges" => [
                [
                    "startDate" => $dateRange,
                    "endDate" => "yesterday"
                ]
            ],
            "dimensions" => [
                [
                    "name" => "date"
                ],
            ],
            "metrics" => [
                [
                    "name" => "activeUsers"
                ]
            ],
            "orderBys" => [
                [
                    "dimension" => [
                        "orderType" => "NUMERIC",
                        "dimensionName" => "date"
                    ]
                ]
            ],
            "metricAggregations" => [
                "TOTAL"
            ]
        ];

        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
        $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
        $responseJson = $analyticsRequest->getResponse();
        $responseData = json_decode($responseJson, true); // Convertit la réponse JSON en tableau associatif
        $userCountsByDate = [];
        if ($responseData != null) {
            foreach ($responseData['rows'] as $row) {
                $date = $row['dimensionValues'][0]['value'];
                $activeUsers = $row['metricValues'][0]['value'];
                $dateTime = DateTimeImmutable::createFromFormat('Ymd', $date);

                $userCountsByDate[] = [
                    'date' => $dateTime,
                    'userCount' => $activeUsers,
                ];
            }
        }
        return [
            "userCountsByDate" => $userCountsByDate,
            "dateRange" => $dateRange
        ];
    }
}
