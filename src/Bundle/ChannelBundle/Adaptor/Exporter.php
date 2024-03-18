<?php

namespace Integrated\Bundle\ChannelBundle\Adaptor;

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
        private readonly LoggerInterface $logger
    ) {
    }

    public function export($content, $state, ChannelInterface $channel, array $settings = []): ?ExporterResponse
    {
        $externalId = null;

        if (!$content instanceof Content || $state != self::STATE_ADD) {
            return null;
        }

        if (!$content->hasChannel($channel)) {
            return null;
        }

        $this->logger->info("Publishing to {$this->connector->getName()}...");
        if ($content->hasConnector($this->config->getId())) {
            return null;
        }

        try {
            $externalId = $this->connector->publish($content, $channel, $this->config->getOptions(), $settings);
        } catch (CouldNotPublish $e) {
            $this->logger->error($e->getMessage()."\n".$e->getTraceAsString());

            return null;
        } catch (\Throwable $e) {
            $this->logger->error($e);
        }

        if (null === $externalId) {
            $this->logger->error('Skipped: refused by connector');

            return null;
        }

        $response = new ExporterResponse($this->config->getId(), $this->config->getAdapter());
        $response->setExternalId($externalId);

        return $response;
    }
}
