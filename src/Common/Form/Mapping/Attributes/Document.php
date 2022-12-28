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

#[\Attribute()]
class Document
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * Constructor.
     *
     * @throws \BadMethodCallException
     */
    public function __construct($exactly = [], string $name = null, array $extra = [])
    {
        if (\is_array($exactly)) {
            $extra = array_merge($exactly, $extra);
            $exactly = $extra['value'] ?? null;
        }

        unset($extra['value']);

        $extra['name'] = $name ?? $exactly ?? $extra['name'] ?? $this->name;

        foreach ($extra as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, static::class));
            }
            $this->$method($value);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
