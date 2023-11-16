<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsRequest;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class SiteActivityWidget implements WidgetInterface
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
        $this->id = 'site_activity';
        $this->name = 'Site activity';
        $this->view = '@IntegratedDashboard/site_activity.html.twig';
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
        $dateRange = $request->query->get('site_activity_date_range') ?? "30daysAgo";
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $brand->profile->analytics;
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            return ["SiteActivity" => "No data found"];
        }
        $allDatas = $this->getDataFromAnalytics($propertyId, $dateRange);
        $siteActivity = $allDatas['siteActivity'];
        $totalViews = $allDatas['siteTotals']['totalUser'];
        $bounceRate = $allDatas['siteTotals']['bounceRate'];
        $viewByCountry = $this->getViewByCountry($siteActivity, $totalViews);

        return [
            "widget" => $this,
            "totalViews" => $totalViews,
            "bounceRate" => $bounceRate,
            "viewByCountry" => $viewByCountry,
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function getDataFromAnalytics(string $propertyId, string $dateRange): array
    {
        $requestBody = [
            "dateRanges" => [
                [
                    "startDate" => "$dateRange",
                    "endDate" => "today"
                ]
            ],
            "dimensions" => [
                [
                    "name" => "country"
                ]
            ],
            "metrics" => [
                [
                    "name" => "bounceRate"
                ],
                [
                    "name" => "totalUsers"
                ]
            ],
            "orderBys" => [
                [
                    "metric" => [
                        "metricName" => "totalUsers"
                    ],
                    "desc" => true
                ]
            ],
            "metricAggregations" => [
                "TOTAL"
            ]
        ];
        $analyticsRequest = new AnalyticsRequest($this->credential, $this->logger);
        $analyticsRequest->GoogleAnalyticsPostRequest($requestBody, $propertyId);
        $responseData = $analyticsRequest->getResponse();
        $allDatas = [];
        if ($responseData != null) {
            $siteActivity = $this->getSiteActivity($responseData);
            $siteTotals = $this->getSiteTotals($responseData);
            $allDatas = [
             'siteActivity' => $siteActivity,
             'siteTotals' => $siteTotals,
            ];
        }

        return $allDatas;
    }
    private function getSiteActivity(array $responseData): array
    {
        $siteActivity = [];
        foreach ($responseData['rows'] as $row) {
            $country = $row['dimensionValues'][0]['value'];
            $bounceRate = (int)$row['metricValues'][0]['value'];
            $totalUser = (int)$row['metricValues'][1]['value'];
            $siteActivity[] = [
                'country' => $country,
                'bounceRate' => round($bounceRate * 100,2),
                'totalUser' => $totalUser,
            ];
        }
        return $siteActivity;
    }

    private function getSiteTotals(array $responseData): array
    {
        $totalsData = $responseData['totals'][0];
        return [
            'bounceRate' => round(($totalsData['metricValues'][0]['value'] * 100),2),
            'totalUser' => $totalsData['metricValues'][1]['value'],
            ];
    }

    private function getViewByCountry(array $sortedCountries, float $totalVisits): array
    {
        usort($sortedCountries, function ($a, $b) {
            return $b['totalUser'] - $a['totalUser'];
        });

        $topCountries = array_slice($sortedCountries, 0, 9, true);
        $otherCountries = array_slice($sortedCountries, 9);

        foreach ($topCountries as $country => $activity) {
            $percentage = round((($activity['totalUser'] / $totalVisits) * 100),2);
            $viewByCountry[$country] = [
                'country' => $activity['country'],
                'totalUser' => $activity['totalUser'],
                'percentage' => $percentage,
            ];
        }
        $otherVisits = 0;
        foreach ($otherCountries as $activity) {
            $otherVisits += $activity['totalUser'];
        }
        $viewByCountry['other'] = [
            'country' => 'other',
            'totalUser' => $otherVisits,
            'percentage' => ($otherVisits / $totalVisits) * 100,
        ];
        return $viewByCountry;
    }

}
