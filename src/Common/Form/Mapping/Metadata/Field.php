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

use Integrated\Common\Form\Mapping\AttributeEditorInterface;

/**
 * Class for storing metadata properties of a field.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class Field implements AttributeEditorInterface
{
    /**
     * @var string
     */
    protected $name;

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
    protected $icon = '';

    /**
     * @var string
     */
    protected $state = '';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
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

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOption($name)
    {
        return $this->hasOption($name) ? $this->options[$name] : null;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }
}
