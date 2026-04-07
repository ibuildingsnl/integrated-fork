<?php

namespace Integrated\Bundle\TaxonomyBundle\Services;

final class TaxonomyOptions
{
    public function __construct(
        public readonly string $root = 'root',
        public readonly int $offset = 0,
        public readonly int $limit = \PHP_INT_MAX,
        public readonly bool $includeUsageCounts = true,
    ) {
    }

    public static function filter(string $root): self
    {
        return new self($root);
    }

    public static function page(int $page, int $pageSize): self
    {
        return new self('root', ($page - 1) * $pageSize, $pageSize);
    }

    public function withoutUsageCounts(): self
    {
        return new self($this->root, $this->offset, $this->limit, false);
    }
}
