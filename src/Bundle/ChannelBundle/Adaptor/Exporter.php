<?php

namespace Integrated\Bundle\ChannelBundle\Adaptor;

use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Bundle\ChannelBundle\Model\CouldNotPublish;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Bundle\ChannelBundle\Model\ConfigInterface;
use Integrated\Common\Channel\Connector\ExporterInterface;
use Integrated\Common\Channel\Exporter\ExporterResponse;
use Psr\Log\LoggerInterface;

final class Exporter implements ExporterInterface
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly ConfigInterface $config,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function export($content, $state, ChannelInterface $channel, array $settings = []): ?ExporterResponse
    {
        if (!$content instanceof Content || $state != self::STATE_ADD) {
            return null;
        }

        // @todo better error handling...
        dump("Publishing to {$this->connector->getName()}...\n");
        if ($content->hasConnector($this->config->getId())) {
            // already posted
            dump("Skipped: already posted");
            return null;
        }

        try {
            $externalId = $this->connector->publish($content, $channel, $this->config->getOptions(), $settings);
        } catch (CouldNotPublish $e) {
            $this->logger->error($e->getMessage() . "\n" . $e->getTraceAsString());
            dump("Failed: {$e->getMessage()}");
//            @todo Add feedback about failure to publication or content
            return null;
        } catch (\Throwable $e) {
            dump('Error:');
            dd($e);
        }

        if (null === $externalId) {
            dump("Skipped: refused by connector");
            return null;
        }

//        @todo Add feedback about success state to publication or content

        $response = new ExporterResponse($this->config->getId(), $this->config->getAdapter());
        $response->setExternalId($externalId);

        return $response;
    }
}
