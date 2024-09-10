<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Form\Mapping\Metadata;

use Integrated\Common\Form\Mapping\AttributeInterface;
use Integrated\Common\Form\Mapping\MetadataEditorInterface;

/**
 * Class for storing metadata properties of a Document.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class Document implements MetadataEditorInterface
{
    /**
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var AttributeInterface[]
     */
    protected $fields = [];

    /**
     * @var AttributeInterface[]
     */
    protected $options = [];

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function isTypeOf($class)
    {
        if (null === $class) {
            return true;
        }

        $reflection = $this->getReflection();

        return (
            (interface_exists($class) && $reflection->implementsInterface($class))
            || $reflection->isSubclassOf($class)
            || (class_exists($class) && $reflection->isInstance(new $class()))
        )
            && $reflection->isInstantiable();
    }

    public function getReflection()
    {
        if ($this->reflection === null) {
            $this->reflection = new \ReflectionClass($this->class);
        }

        return $this->reflection;
    }

    /**
     * Get the class of the Document.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getField($name)
    {
        return $this->hasField($name) ? $this->fields[$name] : null;
    }

    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    public function newField($name)
    {
        return new Field($name);
    }

    public function addField(AttributeInterface $field)
    {
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name)
    {
        return $this->hasOption($name) ? $this->options[$name] : null;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    public function newOption($name)
    {
        return new Field($name);
    }

    public function addOption(AttributeInterface $option)
    {
        $this->options[$option->getName()] = $option;

        return $this;
    }
}
