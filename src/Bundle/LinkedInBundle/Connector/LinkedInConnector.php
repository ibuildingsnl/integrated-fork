<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use GuzzleHttp\Exception\ClientException;
use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

final class LinkedInConnector implements ConnectorInterface
{
    public const NAME = 'linkedin';

    public function __construct(
        private readonly LinkedInFactory $factory,
        private readonly LinkMaker $linkMaker,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        $linkedinVersion = '202309';
        $linkedinPostArticleUrl = 'https://api.linkedin.com/rest/posts';
        $authorUrn = $options->get('page');

        if (!$options->has('token')) {
            throw new CouldNotPublish('An access token is required to create a LinkedIn exporter');
        }

        $client = $this->factory->createClient($options->get('token'));
        $message = $settings['title']. " " .$settings['text']. " " . $this->linkMaker->urlFor($content, $channel);

        $requestOptions['headers'] = [
            'LinkedIn-Version' => $linkedinVersion,
            'X-Restli-Protocol-Version' => '2.0.0',
            // @TODO
            'Cookie' => 'lidc="b=TB74:s=T:r=T:a=T:p=T:g=3873:u=246:x=1:i=1696253933:t=1696335588:v=2:' .
                'sig=AQEXD88VnyHqy_viJAtYHJ8KTJUl3teJ"; lidc="b=TB74:s=T:r=T:a=T:p=T:g=3878:u=248:x=1:' .
                'i=1696404063:t=1696487562:v=2:sig=AQGWyk189Wd1cZaJcPTFnoDJPNX8moEn"; bcookie="v=2&23a437ae-3da8-47c3-8018-cbe1c396531b"',
        ];

        $requestOptions['body'] = '{
            "author": "urn:li:organization:'.$authorUrn.'",
            "commentary": "'.$message.'",
            "visibility": "LOGGED_IN",
            "distribution": {
              "feedDistribution": "MAIN_FEED",
              "targetEntities": [],
              "thirdPartyDistributionChannels": []
            },
            "lifecycleState": "PUBLISHED",
            "isReshareDisabledByAuthor": false
        }';

        $request = $client->getAuthenticatedRequest('POST', $linkedinPostArticleUrl, $options->get('token'), $requestOptions);

        try {
            $response = $client->getResponse($request);
        } catch (ClientException $e) {
            dump('Code:');
            dump($e->getCode());
            dump('Message:');
            dump($e->getMessage());
        }

        if (!isset($response->getHeaders()['x-restli-id'][0])) {
            throw new CouldNotPublish('Could not publish to LinkedIn: '.$this->getErrorFromResponse($response));
        }

        return str_replace('urn:li:share:', '', $response->getHeaders()['x-restli-id'][0]);
    }

    private function getErrorFromResponse(array $response): string
    {
        return $response['errors'][0]['message'] ?? $response['detail'] ?? var_export($response, true);
    }
}
