<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;


class MostReadWidget implements WidgetInterface
{
    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly string          $credential,
        private readonly LoggerInterface $logger,
        private readonly BrandRepository $brandRepository
    )
    {
        $this->id = 'most_read';
        $this->name = 'Most read';
        $this->view = '@IntegratedDashboard/most_read.html.twig';
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
        $dateRange = $request->query->get('most_read_date_range') ?? "30daysAgo";
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            return ["mostViewedPages" => "No data found"];
        }

        $mostViewedPages = $this->getDataFromAnalytics($propertyId, $dateRange);

        return [
            "widget" => $this,
            "mostViewedPages" => $mostViewedPages,
            "dateRange" => $dateRange
        ];
    }
    public function getDataFromAnalytics(string $propertyId, string $dateRange): array
    {
        $requestBody = [
            "dateRanges" => [
                [
                    "startDate" => "$dateRange",
                    "endDate" => "yesterday"
                ]
            ],
            "dimensions" => [
                [
                    "name" => "pageTitle"
                ]
            ],
            "metrics" => [
                [
                    "name" => "screenPageViews"
                ]
            ],
            "orderBys" => [
                [
                    "metric" => [
                        "metricName" => "screenPageViews"
                    ],
                    "desc" => true
                ]
            ],
            "limit" => 5 //why not set this in services.xml since you did it for Latest articles also?
        ];
        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
        $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
        $responseData = $analyticsRequest->getResponse();
        $mostViewedPages = [];
        if ($responseData != null) {
            foreach ($responseData['rows'] as $row) {
                $pageTitle = $row['dimensionValues'][0]['value'];
                $screenPageViews = (int)$row['metricValues'][0]['value'];

                $mostViewedPages[] = [
                    'title' => $pageTitle,
                    'views' => $screenPageViews,
                ];
            }
        }
        return $mostViewedPages;
    }
}
