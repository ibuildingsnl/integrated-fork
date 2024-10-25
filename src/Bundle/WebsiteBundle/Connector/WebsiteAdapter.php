<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Connector;

use Integrated\Common\Channel\Connector\Adapter\ManifestInterface;
use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\ConfigurableInterface;
use Integrated\Common\Channel\Connector\ConfigurationInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class WebsiteAdapter implements AdapterInterface, ConfigurableInterface
{
    /**
     * @var ManifestInterface
     */
    private $manifest;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ManifestInterface $manifest, ConfigurationInterface $configuration)
    {
        $this->manifest = $manifest;
        $this->configuration = $configuration;
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }
}
