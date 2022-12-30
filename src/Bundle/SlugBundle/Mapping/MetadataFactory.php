<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SlugBundle\Mapping;

use Integrated\Bundle\SlugBundle\Mapping\Driver\DriverRegistry;
use Integrated\Bundle\SlugBundle\Mapping\Metadata\ClassMetadata;

class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var DriverRegistry
     */
    private $registry;

    /**
     * @var ClassMetadataInterface[]
     */
    private $data = [];

    public function __construct(DriverRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getMetadata(string $class): ClassMetadataInterface
    {
        if (isset($this->data[$class])) {
            return $this->data[$class];
        }

        return $this->data[$class] = $this->loadMetadata($class);
    }

    private function loadMetadata(string $class): ClassMetadataInterface
    {
        $metadata = new ClassMetadata();

        foreach ($this->registry->getDrivers() as $driver) {
            $driver->loadMetadataForClass($class, $metadata);
        }

        return $metadata;
    }
}
