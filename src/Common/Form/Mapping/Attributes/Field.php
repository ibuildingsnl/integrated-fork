<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Form\Mapping\Attributes;

use Symfony\Component\Form\Extension\Core\Type\TextType;

#[\Attribute()]
class Field
{
    /**
     * @var string
     */
    protected $type = TextType::class;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @throws \BadMethodCallException
     */
    public function __construct($exactly = null, string $type = null, array $options = null, array $extra = [])
    {
        if (\is_array($exactly)) {
            $extra = array_merge($exactly, $extra);
        }

        unset($extra['value']);

        $extra['type'] = $type ?? $extra['type'] ?? $this->type;
        $extra['options'] = $options ?? $extra['options'] ?? $this->options;

        foreach ($extra as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, static::class));
            }
            $this->$method($value);
        }
    }

    /**
     * Get the type of the field.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of the field.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the options of the field.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the label of the field.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
