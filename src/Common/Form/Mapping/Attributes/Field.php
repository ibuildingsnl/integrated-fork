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
    public const LOCATION_EDITOR = 'editor';
    public const LOCATION_SIDEBAR = 'sidebar';
    public const LOCATION_CUSTOM = 'custom';

    /**
     * @var string
     */
    private $type = TextType::class;

    /**
     * @var array
     */
    private $options = [];

    private string $location = self::LOCATION_EDITOR;

    /**
     * Constructor.
     *
     * @throws \BadMethodCallException
     */
    public function __construct($exactly = null, string $type = null, array $options = null, string $location = null, array $extra = [])
    {
        if (\is_array($exactly)) {
            $extra = array_merge($exactly, $extra);
        }

        unset($extra['value']);

        $extra['type'] = $type ?? $extra['type'] ?? $this->type;
        $extra['options'] = $options ?? $extra['options'] ?? $this->options;

        $location = $location ?? $extra['location'] ?? $this->location;

        $extra['location'] = self::LOCATION_SIDEBAR === $location ? self::LOCATION_SIDEBAR : self::LOCATION_EDITOR;

        foreach ($extra as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, static::class));
            }
            $this->$method($value);
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }
}
