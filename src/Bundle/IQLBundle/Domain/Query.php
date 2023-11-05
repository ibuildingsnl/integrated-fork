<?php

namespace Integrated\Bundle\IQLBundle\Domain;

use Stratadox\Sorting\Contracts\Sorting;
use Stratadox\Sorting\NoSorting;
use Stratadox\Specification\Contract\Satisfiable;

final class Query
{
    public function __construct(
        public readonly Satisfiable $condition,
        public readonly Sorting $sorting,
        public readonly ?int $limit = null,
    ) {
    }

    public static function filter(Satisfiable $condition): self
    {
        return new self($condition, NoSorting::needed());
    }
}
