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
            return ["mostViewedPages" => "No data found"];
        }
        $requestBody = [
            "dateRanges" => [
                [
                    "startDate" => '365daysAgo',
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
                //$dateTime = DateTimeImmutable::createFromFormat('d/m/Y"', $date);

                $userCountsByDate[] = [
                    'date' => $date,
                    'userCount' => $activeUsers,
                ];
            }
            $userCountsByDate = array_reverse($userCountsByDate);
        }
        return [
            "widget" => $this,
            "userCountsByDate" => $userCountsByDate,
        ];
    }
}
