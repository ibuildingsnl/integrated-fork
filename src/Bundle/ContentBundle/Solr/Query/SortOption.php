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

class SortOption
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $field,
        public readonly string $order,
    ) {
    }

    public static function create(string $name, string $label, string $field, string $oder): static
    {
        return new self($name, $label, $field, $oder);
    }
}
