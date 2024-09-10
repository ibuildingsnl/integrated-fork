<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Connector\Config;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Options implements OptionsInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Constructor.
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function toArray()
    {
        return $this->data;
    }

    public function set($key, $value)
    {
        if ($value === null) {
            return $this->remove($key);
        }

        $this->data[$key] = $value;

        return $this;
    }

    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function remove($key)
    {
        unset($this->data[$key]);

        return $this;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function clear()
    {
        $this->data = [];

        return $this;
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }
}
