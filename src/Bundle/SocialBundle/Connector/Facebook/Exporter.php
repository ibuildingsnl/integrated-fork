<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SocialBundle\Connector\Facebook;

use Integrated\Bundle\ChannelBundle\Model\ConfigInterface;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\PageBundle\Services\UrlResolver;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\Channel\Connector\ExporterInterface;
use Integrated\Common\Channel\Exporter\ExporterResponse;
use JanuSoftware\Facebook\Facebook;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Exporter implements ExporterInterface
{
    private Facebook $facebook;

    private ConfigInterface $config;

    private UrlResolver $urlResolver;

    public function __construct(Facebook $facebook, ConfigInterface $config, UrlResolver $urlResolver)
    {
        $this->facebook = $facebook;
        $this->config = $config;
        $this->urlResolver = $urlResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function export(object $content, string $state, ChannelInterface $channel): ?ExporterResponse
    {
        if (!$content instanceof Article) {
            return null;
        }

        if ($state != self::STATE_ADD) {
            return null;
        }

        if ($content->hasConnector($this->config->getId())) {
            return null;
        }

        try {
            $page = $this->config->getOptions()->get('page');
            $postResponse = $this->facebook->post(
                '/'.$page.'/feed',
                [
                    'link' => 'https://'.$channel->getPrimaryDomain().$this->urlResolver->generateUrl($content, $channel->getId()),
                    'message' => $content->getTitle(),
                ],
                $this->config->getOptions()->get('page_token'),
                null,
                'v3.2'
            );

            $graphNode = $postResponse->getGraphNode();
        } catch (\Exception $e) {
            // @todo probably should log this somewhere INTEGRATED-995
            return null;
        }

        $response = new ExporterResponse($this->config->getId(), $this->config->getAdapter());
        $response->setExternalId($graphNode['id']);

        return $response;
    }
}
