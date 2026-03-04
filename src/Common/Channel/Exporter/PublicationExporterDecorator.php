<?php

namespace Integrated\Common\Channel\Exporter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Connector;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ResolverInterface;
use Integrated\Common\Channel\Connector\ExporterInterface;
use Integrated\Common\Channel\Connector\ExporterInterface as ConnectorExporterInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

class PublicationExporterDecorator implements ExporterInterface
{
    /**
     * @var array<string, ConnectorExporterInterface[]>
     */
    private array $cache = [];

    public function __construct(
        private readonly ExporterInterface $exporter,
        private readonly RegistryInterface $registry,
        private readonly ResolverInterface $resolver,
        private readonly DocumentManager $manager,
        private readonly PublicationRepositoryInterface $repository,
    ) {
    }

    public function export($content, $state, ChannelInterface $channel, array $settings = []): ?ExporterResponse
    {
        if (!$content instanceof Content) {
            return $this->exporter->export($content, $state, $channel, $settings);
        }

        if (!$content->isPublished()) {
            $state = ConnectorExporterInterface::STATE_DELETE;
        }

        $now = new \DateTimeImmutable('now');

        $old = true;
        foreach ($this->repository->getAvailable($content, $channel) as $publication) {
            $settings = $publication->getSettings();
            $start = $publication->getTime()->getStartDate();

            if ($start > $now) {
                $state = ConnectorExporterInterface::STATE_DELETE;
            }

            foreach ($this->getExporters($channel) as $exporter) {
                if ($response = $exporter->export($content, $state, $channel, $settings)) {
                    $this->save($content, $response);
                } else {
                    $this->manager->flush();
                }
            }

            $old = false;
        }

        if ($old) {
            return $this->exporter->export($content, $state, $channel, $settings);
        } else {
            return null;
        }
    }

    /**
     * @return ConnectorExporterInterface[]
     */
    protected function getExporters(ChannelInterface $channel): array
    {
        if (!\array_key_exists($channel->getId(), $this->cache)) {
            $exporters = [];
            $now = new \DateTimeImmutable('now');

            foreach ($this->resolver->getConfigs($channel) as $config) {
                $publicationStartDate = $config->getPublicationStartDate();
                if ($publicationStartDate && $publicationStartDate > $now) {
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

    protected function save(Content $content, ExporterResponse $response): void
    {
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

        $this->manager->persist($content);
        $this->manager->flush();
    }
}
