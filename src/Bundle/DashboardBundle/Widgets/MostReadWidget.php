<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Doctrine\ODM\MongoDB\DocumentManager;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\UserBundle\Model\User;
use \Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;


class MostReadWidget implements WidgetInterface
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
        return 'most_read';
    }
    public function name(): string
    {
        return 'most read';
    }

    public function view(): string
    {
        return '@IntegratedDashboard/most_read.html.twig';
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
            "limit" => 20
        ];
        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
        $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
        $responseJson = $analyticsRequest->getResponse();
        $data = json_decode($responseJson, true);
        $mostViewedPages = [];
        if ($data != null) {
            foreach ($data['rows'] as $row) {
                $pageTitle = $row['dimensionValues'][0]['value'];
                $screenPageViews = (int)$row['metricValues'][0]['value'];

                $mostViewedPages[] = [
                    'title' => $pageTitle,
                    'views' => $screenPageViews,
                ];
            }
        }
        return [
            "mostViewedPages" => $mostViewedPages,
            "dateRange" => $dateRange
        ];
    }
}
