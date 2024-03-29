<?php

namespace Integrated\Bundle\ChannelBundle\Adaptor;

use GuzzleHttp\Exception\ClientException;
use Integrated\Bundle\ChannelBundle\Model\ConfigInterface;
use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\Connector\ExporterInterface;
use Integrated\Common\Channel\Exporter\ExporterResponse;
use Integrated\Common\Content\Channel\ChannelInterface;
use Psr\Log\LoggerInterface;

final class Exporter implements ExporterInterface
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly ConfigInterface $config,
        private readonly LoggerInterface $logger,
        private readonly PublicationRepositoryInterface $publications
    ) {
    }

    public function export($content, $state, ChannelInterface $channel, array $settings = []): ?ExporterResponse
    {
        if (!$content instanceof Content || $state != self::STATE_ADD) {
            return null;
        }

        if (!$content->hasChannel($channel)) {
            return null;
        }

        $this->logger->info("Publishing to {$this->connector->getName()}...");
        if ($content->hasConnector($this->config->getId())) {
            foreach ($this->publications->forContentOnChannel($content, $channel) as $publication) {
                $publication->setResponse('Content already published on this connector');
                $publication->setStatus('failed');
            }

            return null;
        }

        $responseMessage = null;
        $externalId = null;
        $status = 'failed';

        try {
            $externalId = $this->connector->publish($content, $channel, $this->config->getOptions(), $settings);
        } catch (CouldNotPublish $e) {
            $this->logger->error($e->getMessage()."\n".$e->getTraceAsString());
            $responseMessage = $e->getMessage();
        }

        if (null === $externalId) {
            $this->logger->error('Skipped: refused by connector');
        }

        $response = new ExporterResponse($this->config->getId(), $this->config->getAdapter());
        if ($externalId !== null) {
            $response->setExternalId($externalId);
            $responseMessage = $externalId;
            $status = 'success';
        }
        foreach ($this->publications->forContentOnChannel($content, $channel) as $publication) {
            $publication->setResponse($responseMessage);
            $publication->setStatus($status);
        }

        return $responseMessage instanceof ExporterResponse ? $responseMessage : null;
    }
}
