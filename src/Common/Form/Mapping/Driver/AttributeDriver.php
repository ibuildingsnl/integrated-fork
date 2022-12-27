<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Form\Mapping\Driver;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Integrated\Common\Form\Mapping\Attributes\Document;
use Integrated\Common\Form\Mapping\Attributes\Field;
use Integrated\Common\Form\Mapping\DriverInterface;
use Integrated\Common\Form\Mapping\MetadataEditorInterface;
use Integrated\Common\Mapping\Reader\AttributeReader;

class AttributeDriver implements DriverInterface
{
    protected MappingDriver $driver;

    private AttributeReader $reader;

    public function __construct(MappingDriver $driver)
    {
        $this->driver = $driver;
        $this->reader = new AttributeReader();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames(): array
    {
        return array_filter($this->driver->getAllClassNames(), function (string $class) {
            return $this->isSupported($class);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(MetadataEditorInterface $metadata): void
    {
        /* @var $document Document */
        $document = $this->reader->getClassAttribute($metadata->getReflection(), Document::class);

        if ($document == null) {
            return;
        }

        $metadata->setType($document->getName());

        foreach ($metadata->getReflection()->getProperties() as $prop) {
            /* @var $field Field */
            $field = $this->reader->getPropertyAttribute($prop, Field::class);

            if ($field == null) {
                continue;
            }

            $metadataField = $metadata->newField($prop->getName())
                ->setType($field->getType())
                ->setOptions($field->getOptions());

            $metadata->addField($metadataField);
        }
    }

    public function isSupported(string $class): bool
    {
        $reflection = new \ReflectionClass($class);

        return (bool) $this->reader->getClassAttribute($reflection, Document::class);
    }
}
