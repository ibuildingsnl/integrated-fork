<?php

namespace Integrated\Bundle\ChannelBundle\Model;

use Integrated\Bundle\ChannelBundle\Event\ConfigEvent;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;

interface OauthConfigInterface extends ConnectorConfigInterface
{
    /** @throws ConfigurationException */
    public function prepareAuthLink(ConfigEvent $event, OptionsInterface $options): ?string;

    /** @throws ConfigurationException */
    public function handleCallback(ConfigEvent $event, OptionsInterface $options): bool;
}
