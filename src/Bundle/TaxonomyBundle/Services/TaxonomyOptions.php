<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

final class TaxonomyOptions
{
    public function __construct(
        public readonly string $root = 'root',
        public readonly int $page = 1,
        public readonly int $pageSize = 50,
    ) {
    }

    public static function filter(string $root): self
    {
        return new self($root);
    }

    public static function page(int $page, int $pageSize): self
    {
        return new self('root', $page, $pageSize);
    }
}
