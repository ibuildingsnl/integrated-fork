<?php

namespace Integrated\Bundle\DashboardBundle\Widgets;

use Integrated\Bundle\AnalyticsBundle\Infrastructure\AnalyticsDataFetcher;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client as GuzzleClient;
use Google\Client as GoogleApiClient;

use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;

use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Core\RestTrait;

class MostReadWidget implements WidgetInterface
{

    protected const SLUGS = [
        'article' => 'artikel',
        'author' => 'auteur',
        'news' => 'nieuws',
        'notes' => 'notitie',
        'partner' => 'partner',
        'podcast' => 'podcast',
        'video' => 'video',
        'webinar' => 'webinar',
    ];

    protected string $propertyId = '348963659';


    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string          $credential
    )
    {
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
     * @throws ValidationException
     * @throws ApiException
     */
    public function params(ChannelInterface $channel, User $user): array
    {
        $mostRead = [];
        $requestBody = [
            "dateRanges" => [
                [
                    "startDate" => "30daysAgo",
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

        $responseJson = $this->GooggleAnalyticsPostRequest($requestBody);
        // Convertir la réponse JSON en tableau associatif
        $data = json_decode($responseJson, true);

        // Initialiser un tableau pour stocker les données finales
        $mostViewedPages = [];

        // Parcourir les lignes de la réponse
        foreach ($data['rows'] as $row) {
            $pageTitle = $row['dimensionValues'][0]['value'];
            $screenPageViews = (int) $row['metricValues'][0]['value'];

            // Stocker les données dans le tableau associatif
            $mostViewedPages[] = [
                'title' => $pageTitle,
                'views' => $screenPageViews,
            ];
        }
        return [
            "mostViewedPages" => $mostViewedPages
        ];
    }
    function getAccessToken($googleCredentialPath)
    {
        $GoogleApiClient = new GoogleApiClient();
        $GoogleApiClient->setAuthConfig($googleCredentialPath);
        $GoogleApiClient->addScope('https://www.googleapis.com/auth/analytics.readonly');
        $GoogleApiClient->useApplicationDefaultCredentials();

        $token = $GoogleApiClient->fetchAccessTokenWithAssertion();

        return $token['access_token'];
    }

    public function GooggleAnalyticsPostRequest(array $requestBody): ?string
    {
        $propertyId = $this->propertyId;
        $googleCredentialPath = $this->credential;
        $accessToken = $this->getAccessToken($googleCredentialPath);

        $apiUrl = "https://analyticsdata.googleapis.com/v1beta/properties/$propertyId:runReport";

        $guzzleClient = new GuzzleClient();

        $response = $guzzleClient->request('POST', $apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json' => $requestBody,
        ]);

        $responseBody = $response->getBody()->getContents();

        return $responseBody;
    }



}
