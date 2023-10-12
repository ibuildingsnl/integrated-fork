<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ChannelBundle\Services\LinkMaker;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

final class LinkedInConnector implements ConnectorInterface
{
    public const NAME = 'LinkedIn';

    public function __construct(
        private readonly LinkedInFactory $factory,
        private readonly LinkMaker $linkMaker,
    ) {}

    public function getName(): string
    {
        return self::NAME;
    }

    public function publish(Content $content, ChannelInterface $channel, OptionsInterface $options, array $settings): ?string
    {
        //todo, decide where to put vars
        $linkedinVersion = '202309';
        $linkedinPostArticleUrl = 'https://api.linkedin.com/rest/posts';
        $linkedinAuthor = '98903555';

        if (!$options->has('token')) {
            throw new CouldNotPublish('An access token is required to create a LinkedIn exporter');
        }

        $client = $this->factory->createClient($options->get('token'));

        $message = $settings['foo'] ?? '';
        if (!empty($message)) {
            $message .= "\n\n";
        }

        $requestOptions['headers'] = [
            'LinkedIn-Version' => $linkedinVersion,
            'X-Restli-Protocol-Version' => '2.0.0',
            'Cookie' => 'lidc="b=TB74:s=T:r=T:a=T:p=T:g=3873:u=246:x=1:i=1696253933:t=1696335588:v=2:sig=AQEXD88VnyHqy_viJAtYHJ8KTJUl3teJ"; lidc="b=TB74:s=T:r=T:a=T:p=T:g=3878:u=248:x=1:i=1696404063:t=1696487562:v=2:sig=AQGWyk189Wd1cZaJcPTFnoDJPNX8moEn"; bcookie="v=2&23a437ae-3da8-47c3-8018-cbe1c396531b"'
        ];

        $requestOptions['body'] = '{
            "author": "urn:li:organization:' . $linkedinAuthor . '",
            "commentary": ' . $message . ',
            "visibility": "LOGGED_IN",
            "distribution": {
              "feedDistribution": "MAIN_FEED",
              "targetEntities": [],
              "thirdPartyDistributionChannels": []
            },
            "lifecycleState": "PUBLISHED",
            "isReshareDisabledByAuthor": false
        }';

        //to add a link to the article, turn it off for now:
        //$message .= "https://".$this->linkMaker->urlFor($content, $channel);

        $response = $client->getAuthenticatedRequest('POST', $linkedinPostArticleUrl, $options->get('token'), $requestOptions);

        dd($response);

        if (!isset($response['data']['id'])) {
            throw new CouldNotPublish('Could not publish to LinkedIn: ' . $this->getErrorFromResponse($response));
        }

        return $response['data']['id'];
    }

    private function getErrorFromResponse(array $response): string
    {
        return $response['errors'][0]['message'] ?? $response['detail'] ?? var_export($response, true);
    }
}
