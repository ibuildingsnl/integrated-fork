<?php

namespace Integrated\Bundle\IQLBundle\Filtering\Double;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\IQLBundle\Domain\Query;
use Stratadox\Sorting\Contracts\Sorter;
use Stratadox\Sorting\ObjectSorter;

final class Publications
{
    public function __construct(
        private array $publications,
        private ?Sorter $sorter,
    ) {
        if (!$this->sorter) {
            $this->sorter = new ObjectSorter();
        }
    }

    public function findBy(Query $query): array
    {
        return array_slice($this->sorter->sort(
            array_filter($this->publications, fn (Publication $p) => $query->condition->isSatisfiedBy($p)),
            $query->sorting
        ), 0, $query->limit);
    }

    public function add(Publication $publication): void
    {
        $this->publications[] = $publication;
    }
}
