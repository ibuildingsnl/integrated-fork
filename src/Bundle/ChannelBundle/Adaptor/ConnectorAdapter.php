<?php

namespace Integrated\Bundle\ChannelBundle\Adaptor;

use Integrated\Bundle\ChannelBundle\Model\ConfigInterface as ModelConfigInterface;
use Integrated\Bundle\ChannelBundle\Model\ConnectorConfigInterface;
use Integrated\Bundle\ChannelBundle\Model\ConnectorInterface;
use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;
use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\ConfigInterface;
use Integrated\Common\Channel\Connector\ConfigurableInterface;
use Integrated\Common\Channel\Connector\ConfigurationInterface;
use Integrated\Common\Channel\Connector\ExporterInterface;
use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Common\Services\Flusher;
use Psr\Log\LoggerInterface;

final class ConnectorAdapter implements AdapterInterface, ConfigurableInterface, ConfigurationInterface, ExportableInterface
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly ConnectorConfigInterface $config,
//        private readonly LoggerInterface $logger,
    ) {
//        dd($logger);
    }

    public function getManifest(): ManifestInterface
    {
        return new ConnectorManifest($this->connector);
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this;
    }

    public function getForm(): string
    {
        return $this->config->getForm();
    }

    public function getExporter(ConfigInterface $config): ExporterInterface
    {
        assert($config instanceof ModelConfigInterface);
        return new Exporter($this->connector, $config, $this->logger);
    }
}
