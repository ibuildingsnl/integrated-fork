<?php

namespace Integrated\Bundle\IQLBundle\Tests\Filtering\Double;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\IQLBundle\Domain\Query;
use Integrated\Bundle\IQLBundle\Infrastructure\PublicationSorter;
use Stratadox\Sorting\Contracts\Sorter;

final class Publications
{
    public function __construct(
        private array $publications = [],
        private ?Sorter $sorter = null,
    ) {
        if (!$this->sorter) {
            $this->sorter = new PublicationSorter();
        }
    }

    /** @return Publication[] */
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
