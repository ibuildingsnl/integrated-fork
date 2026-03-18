<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Form\Mapping;

use Integrated\Common\Form\Mapping\Event\MetadataEvent;
use Integrated\Common\Form\Mapping\Metadata\Document;
use Integrated\Common\Mapping\Registry\DriverRegistry;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var DriverRegistry
     */
    private $registry;

    /**
     * @var string
     */
    private $type;

    /**
     * @var MetadataInterface[]
     */
    protected $data = [];

    public function __construct(DriverRegistry $registry, ?string $type = null)
    {
        $this->registry = $registry;
        $this->type = $type;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return DriverInterface
     *
     * @deprecated
     */
    public function getDriver()
    {
        return $this->registry->getDrivers()[0];
    }

    public function getAllMetadata()
    {
        $metadata = [];

        foreach ($this->registry->getDrivers() as $driver) {
            foreach ($driver->getAllClassNames() as $class) {
                $data = $this->getMetadata($class);

                if ($data->isTypeOf($this->type)) {
                    $metadata[] = $data;
                }
            }
        }

        return $metadata;
    }

    public function getMetadata($class)
    {
        $class = trim((string) $class);
        if ($class === '') {
            return null;
        }

        if (isset($this->data[$class])) {
            return $this->data[$class];
        }

        return $this->data[$class] = $this->loadMetadata($class);
    }

    /**
     * @param string $class
     *
     * @return MetadataEditorInterface
     */
    public function newMetadata($class)
    {
        return new Document($class);
    }

    /**
     * @param string $class
     *
     * @return MetadataEditorInterface
     */
    protected function loadMetadata($class)
    {
        $metadata = $this->newMetadata($class);

        if ($metadata->isTypeOf($this->type)) {
            foreach ($this->registry->getDrivers() as $driver) {
                if ($driver->isSupported($class)) {
                    $driver->loadMetadataForClass($metadata);
                    $this->getEventDispatcher()->dispatch(new MetadataEvent($metadata), Events::METADATA);
                }
            }
        }

        return $metadata;
    }
}
