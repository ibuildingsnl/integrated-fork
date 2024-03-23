<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Exporter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Connector;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ResolverInterface;
use Integrated\Common\Channel\Connector\ExporterInterface as ConnectorExporterInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\ConnectableInterface;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\PublishableInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Exporter implements ExporterInterface
{
    /**
     * @var ConnectorExporterInterface[][]
     */
    private $cache = [];

    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly ResolverInterface $resolver,
        private readonly DocumentManager $dm,
        private readonly PublicationRepositoryInterface $publications
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function export($content, $state, ChannelInterface $channel, array $settings = [])
    {
        if ($content instanceof Content) {
            foreach ($this->publications->forContentOnChannel($content, $channel) as $publication) {
                $settings = $publication->getSettings();
                $time = $publication->getTime();

                $startDate = $time->getStartDate();
                $now = new \DateTime('now', new \DateTimeZone('UTC')); // Ensure time zone consistency

                if ($startDate > $now) {
                    $state = ConnectorExporterInterface::STATE_DELETE;
                }

                if ($publication->getStatus() === 'succes') {
                    return;
                }
            }
        }

        $publicationDate = null;
        if ($content instanceof PublishableInterface) {
            $publicationDate = $content->getPublishTime()->getStartDate();
            if (!$content->isPublished()) {
                // make sure content is not published when publication date or state
                // has changed after queueing
                $state = ConnectorExporterInterface::STATE_DELETE;
            }
        }

        if (count($this->getExporters($channel, $publicationDate)) === 0) {
            foreach ($this->publications->forContentOnChannel($content, $channel) as $publication) {
                $publication->setStatus('failed');
                $publication->setResponse('There is no connector configured, please check your settings');
            }
        }

        foreach ($this->getExporters($channel, $publicationDate) as $exporter) {
            $response = $exporter->export($content, $state, $channel, $settings);

            if ($response instanceof ExporterResponse) {
                $this->save($content, $response);
            }

            $this->dm->flush();
        }
    }

    /**
     * @param ?\DateTime $publicationDate
     *
     * @return ConnectorExporterInterface[]
     */
    protected function getExporters(ChannelInterface $channel, $publicationDate)
    {
        if (!\array_key_exists($channel->getId(), $this->cache)) {
            $exporters = [];

            foreach ($this->resolver->getConfigs($channel) as $config) {
                $publicationStartDate = $config->getPublicationStartDate();
                if ($publicationStartDate && $publicationDate && $publicationStartDate > $publicationDate) {
                    continue;
                }

                $adaptor = $this->registry->getAdapter($config->getAdapter());

                if ($adaptor instanceof ExportableInterface) {
                    $exporters[] = $adaptor->getExporter($config);
                }
            }

            $this->cache[$channel->getId()] = $exporters;
        }

        return $this->cache[$channel->getId()];
    }

    /**
     * @param object $content
     */
    protected function save($content, ExporterResponse $response)
    {
        if (!$content instanceof ContentInterface) {
            return;
        }

        if (!$content instanceof ConnectableInterface) {
            return;
        }

        if ($content->hasConnector($response->getConfigId())) {
            $content->getConnector($response->getConfigId())
                    ->setConfigAdapter($response->getConfigAdapter())
                    ->setExternalId($response->getExternalId());
        } else {
            $content->addConnector(
                (new Connector())
                    ->setConfigId($response->getConfigId())
                    ->setConfigAdapter($response->getConfigAdapter())
                    ->setExternalId($response->getExternalId())
            );
        }

        $this->dm->persist($content);
        $this->dm->flush();
    }
}
