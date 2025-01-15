<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Converter\Config;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Config implements ConfigInterface
{
    /**
     * @var TypeConfigInterface[]
     */
    private $types;

    /**
     * @var ConfigInterface|null
     */
    private $parent;

    /**
     * Constructor.
     *
     * @param TypeConfigInterface[] $types
     */
    public function __construct(array $types, ?ConfigInterface $parent = null)
    {
        $this->types = $types;
        $this->parent = $parent;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function hasParent()
    {
        return $this->parent !== null;
    }

    public function getParent()
    {
        return $this->parent;
    }
}
