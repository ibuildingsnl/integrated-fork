<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Solr\Query;

class SortOptions
{
    private array $options = [];

    /**
     * @param SortOption[] $options
     */
    public function __construct(array $options)
    {
        foreach ($options as $index => $option) {
            if (!$option instanceof SortOption) {
                throw new \InvalidArgumentException(sprintf('Sorting option "%s" is not an instance of "%s"', $index, SortOption::class));
            }

            $this->options[$option->name] = $option;
        }
    }

    public function has(string $name): bool
    {
        return (bool) ($this->options[$name] ?? false);
    }

    public function get(string $name): SortOption
    {
        if ($this->options[$name] ?? null) {
            return $this->options[$name];
        }

        throw new \InvalidArgumentException(sprintf('Sorting option "%s" does not exist', $name));
    }

    public function all(): array
    {
        return $this->options;
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->options);
    }
}
