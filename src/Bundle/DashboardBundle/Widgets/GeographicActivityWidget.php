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
use DateTimeImmutable;
use Integrated\Bundle\AnalyticsBundle\Document\GeographicActivity;


class GeographicActivityWidget implements WidgetInterface
{

    private readonly string $id;
    private readonly string $name;
    private readonly string $view;

    public function __construct(
        private readonly DocumentManager $manager,
    )
    {
        $this->id = 'geographic_activity';
        $this->name = 'Geographic activity';
        $this->view = '@IntegratedDashboard/geographic_activity.html.twig';
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

    /**
     * @throws GuzzleException
     */
    public function getParams(ChannelInterface $channel, User $user, Request $request): array
    {
        $geographicActivity = $this->manager->getRepository(GeographicActivity::class)
            ->findOneBy(
                ['channelID' => $channel->getId()],
                ['dateTime' => 'DESC']
            );
        $allDatas = $geographicActivity->getGeographicActivity();
        $result = [
            "widget" => $this,
            "totalViews" => [],
            "bounceRate" => [],
            "viewByCountry" => [],
        ];

        //dd($allDatas);
        foreach ($allDatas as $key => $data) {
            if ($data == null) {
                $result['totalViews'][$key] = "No data found";
                $result['bounceRate'][$key] = "No data found";
                $result['viewByCountry'][$key] = [];
            } else {
                $result['totalViews'][$key] = $data['siteTotals']['totalUser'];
                $result['bounceRate'][$key] = $data['siteTotals']['bounceRate'];
                $result['viewByCountry'][$key] = $this->getViewByCountry($data['GeographicActivity'], $result['totalViews'][$key]);
            }
        }
        return $result;
    }

    private function getViewByCountry(array $sortedCountries, float $totalVisits): array
    {
        $viewByCountry = [];
        $viewByCity = [];

        foreach ($sortedCountries as $activity) {
            $country = $activity['country'];
            $city = !empty($activity['city']) ? $activity['city'] : 'other';
            $totalUser = $activity['totalUser'];

            if (!isset($viewByCountry[$country])) {
                $viewByCountry[$country] = [
                    'country' => $country,
                    'totalUser' => 0,
                    'percentage' => 0,
                ];
            }

            $viewByCountry[$country]['totalUser'] += $totalUser;

            if (!isset($viewByCity[$country][$city])) {
                $viewByCity[$country][$city] = [
                    'city' => $city,
                    'totalUser' => 0,
                    'percentage' => 0,
                ];
            }

            $viewByCity[$country][$city]['totalUser'] += $totalUser;
        }

        foreach ($viewByCountry as &$countryData) {
            $percentage = round(($countryData['totalUser'] / $totalVisits) * 100, 2);
            $countryData['percentage'] = $percentage;

            uasort($viewByCity[$countryData['country']], function ($a, $b) {
                return $b['totalUser'] - $a['totalUser'];
            });

            $otherCities = array_slice($viewByCity[$countryData['country']], 9);
            $otherTotalUser = 0;
            foreach ($otherCities as $otherCity) {
                $otherTotalUser += $otherCity['totalUser'];
            }

            $countryData['cities'] = array_slice($viewByCity[$countryData['country']], 0, 9, true);
            $countryData['cities']['Other'] = [
                'city' => 'Other',
                'totalUser' => $otherTotalUser,
                'percentage' => round(($otherTotalUser / $countryData['totalUser']) * 100, 2),
            ];
        }


        uasort($viewByCountry, function ($a, $b) {
            return $b['totalUser'] - $a['totalUser'];
        });

        $topCountries = array_slice($viewByCountry, 0, 9, true);
        $topOtherCountries = $this->processTopCities($sortedCountries, $topCountries);

        $otherCountries = array_slice($viewByCountry, 9);

        $otherVisits = 0;
        foreach ($otherCountries as $activity) {
            $otherVisits += $activity['totalUser'];
        }

        $topCountries['other'] = [
            'country' => 'other',
            'totalUser' => $otherVisits,
            'percentage' => ($otherVisits / $totalVisits) * 100,
            'cities' => $topOtherCountries,
        ];
        $this->processUndefinedCities($topCountries);
        return $topCountries;
    }

    function processUndefinedCities(array &$topCountries): void
    {
        foreach ($topCountries as &$countryData) {
            if (isset($countryData['cities'])) {
                $undefinedCities = [];
                $otherCityData = null;

                foreach ($countryData['cities'] as $cityName => $cityData) {
                    if ($cityName === "Other") {
                        $otherCityData = $cityData;
                        unset($countryData['cities'][$cityName]);
                    } elseif ($cityName === "" || $cityName === "(undefined)" || $cityName === "(not set)") {
                        $undefinedCities[] = $cityData;
                        unset($countryData['cities'][$cityName]);
                    }
                }

                if (!empty($undefinedCities)) {
                    $totalUser = 0;
                    $percentage = 0;

                    foreach ($undefinedCities as $cityData) {
                        $totalUser += $cityData['totalUser'];
                        $percentage += $cityData['percentage'];
                    }

                    if ($otherCityData !== null) {
                        $totalUser += $otherCityData['totalUser'];
                        $percentage += $otherCityData['percentage'];
                    }

                    $countryData['cities']['Other'] = [
                        'city' => 'Other',
                        'totalUser' => $totalUser,
                        'percentage' => $percentage,
                    ];
                }
            }
        }

        $this->calculateCityPercentages($topCountries);
    }

    function processTopCities(array $data, array $topCountries): array
    {
        $citiesData = [];

        // Récupérer la liste des pays du tableau topCountries
        $excludedCountries = array_keys($topCountries);

        foreach ($data as $row) {
            $cityName = $row['city'];
            $countryName = $row['country'];

            // Ignorer les pays du tableau topCountries
            if (in_array($countryName, $excludedCountries)) {
                continue;
            }

            if ($cityName === "" || $cityName === "(undefined)" || $cityName === "(not set)") {
                $cityName = 'Unknown city';
            }
            $fullCityName = $cityName . " (" . $countryName . ")";
            $totalUser = $row['totalUser'];

            if (!isset($citiesData[$cityName])) {
                $citiesData[$cityName] = [
                    'city' => $fullCityName,
                    'totalUser' => $totalUser,
                    'percentage' => 0,
                ];
            } else {
                $citiesData[$cityName]['totalUser'] += $totalUser;
            }
        }

        // Trier le tableau par le nombre total d'utilisateurs de manière décroissante
        usort($citiesData, function ($a, $b) {
            return $b['totalUser'] - $a['totalUser'];
        });

        // Sélectionner les 9 premières villes
        $topCities = array_slice($citiesData, 0, 9, true);
        // Calculer le pourcentage pour chaque ville
        $totalUsers = array_sum(array_column($citiesData, 'totalUser'));
        foreach ($topCities as &$city) {
            $city['percentage'] = ($city['totalUser'] / $totalUsers) * 100;
        }

        // Calculer le cumul des autres villes
        $otherCities = array_slice($citiesData, 9);
        $otherTotalUsers = array_sum(array_column($otherCities, 'totalUser'));

        // Ajouter l'entrée "Other"
        $topCities['Other'] = [
            'city' => 'Other',
            'totalUser' => $otherTotalUsers,
            'percentage' => ($otherTotalUsers / $totalUsers) * 100,
        ];
        return $topCities;
    }


    function calculateCityPercentages(array &$topCountries): void
    {
        foreach ($topCountries as &$countryData) {
            if (isset($countryData['cities'])) {
                $totalUser = $countryData['totalUser'];

                foreach ($countryData['cities'] as &$cityData) {
                    $cityData['percentage'] = round(($cityData['totalUser'] / $totalUser) * 100, 2);
                }
            }
        }
    }
}
