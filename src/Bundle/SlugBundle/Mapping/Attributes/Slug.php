<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SlugBundle\Mapping\Attributes;

use BadMethodCallException;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Slug
{
    /**
     * @var string[]
     */
    public $fields = [];

    /**
     * @var string
     */
    public $separator = '-';

    /**
     * @var int
     */
    public $lengthLimit = 200;

    /**
     * @throws BadMethodCallException
     */
    public function __construct(array $exactly = null, array $fields = null, string $separator = null, int $lengthLimit = null, $extra = [])
    {
        if (\is_array($exactly)) {
            $extra = array_merge($exactly, $extra);
        }

        unset($extra['value']);

        $extra['fields'] = $fields ?? $extra['fields'] ?? null;
        $extra['separator'] = $separator ?? $extra['separator'] ?? null;
        $extra['lengthLimit'] = $lengthLimit ?? $extra['lengthLimit'] ?? null;

        foreach ($extra as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new BadMethodCallException(sprintf("Unknown property '%s' on attribute '%s'.", $key, static::class));
            }
            $this->$method($value);
        }
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    public function getLengthLimit(): int
    {
        return $this->lengthLimit;
    }

    public function setLengthLimit(int $lengthLimit): void
    {
        $this->lengthLimit = $lengthLimit;
    }
}
