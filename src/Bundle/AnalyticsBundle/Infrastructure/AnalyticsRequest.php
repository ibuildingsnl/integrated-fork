<?php

namespace Integrated\Bundle\AnalyticsBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Google\Client as GoogleApiClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Integrated\Bundle\AnalyticsBundle\Document\AnalyticsData;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

class AnalyticsRequest
{

    public function __construct(
        private readonly string $credential,
        private readonly LoggerInterface $logger,
        private readonly BrandRepository  $brandRepository,
        private readonly ObjectRepository $channelRepository,
        private readonly DocumentManager  $manager,
    )
    {
    }
    private string $response;

    public function getResponse()
    {
        return json_decode($this->response, true);
    }

    private function getAccessToken($googleCredentialPath)
    {
        $GoogleApiClient = new GoogleApiClient();
        $GoogleApiClient->setAuthConfig($googleCredentialPath);
        $GoogleApiClient->addScope('https://www.googleapis.com/auth/analytics.readonly');
        $GoogleApiClient->useApplicationDefaultCredentials();

        $token = $GoogleApiClient->fetchAccessTokenWithAssertion();

        return $token['access_token'];
    }

    /**
     * @throws GuzzleException
     */
    public function googleAnalyticsPostRequest(array $requestBody, string $propertyId): void
    {
        try {
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
        } catch (GuzzleException $e) {
            // Handle other Guzzle exceptions here
            $this->logger->error('Get Analytics Error: ' . $e->getMessage(). '\n');
        }
        $this->response = $responseBody ?? "";
    }

    /**
     * @throws GuzzleException
     */
    public function getDataFromAnalytics(ChannelInterface $channel, array $requestBody): ?array
    {
        $propertyID = $this->getPropertyID($channel) ?? null;
        if ($propertyID != null)
        {
            $this->googleAnalyticsPostRequest($requestBody, $propertyID);
            $responseData = $this->getResponse();
            if ($responseData != null && isset($responseData['rows'])) {
                return $responseData;
            }
            return null;
        }
        return null;
    }

    public function getChannels(): array
    {
        $channels = [];
        foreach ($this->channelRepository->findAll() as $channel) {
            if ($channel->getType()->id == 'website') {
                $channels[] = $channel;
            }
        }
        return $channels;
    }
    public function getPropertyID(ChannelInterface $channel): ?string
    {
        foreach ($this->brandRepository->all() as $brand) {
            if ($brand->hasChannel($channel)) {
                $propertyId = $this->extractGoogleAnalyticsID($brand->profile->analytics);
            }
        }
        if (!isset($propertyId) || $propertyId == null) {
            $this->logger->error($channel->getName(). ' Error: no property ID found');
            return null;
        }
        return $propertyId;
    }
    function extractGoogleAnalyticsID($input) {
        preg_match('/\d+/', $input, $matches);
        return $matches[0] ?? null;
    }

    public function setDataToDB(ChannelInterface $channel, $dataType ,$allDatas): void
    {
        $analyticsData = new AnalyticsData($channel->getId(), $dataType, $allDatas, new DateTimeImmutable());
        $this->manager->persist($analyticsData);
        $this->manager->flush();
    }
}
