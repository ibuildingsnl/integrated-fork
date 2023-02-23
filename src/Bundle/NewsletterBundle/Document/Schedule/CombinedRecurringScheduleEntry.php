<?php

namespace Integrated\Bundle\NewsletterBundle\Document\Schedule;

use Doctrine\Common\Collections\ArrayCollection;

class CombinedRecurringScheduleEntry implements RecurringScheduleEntry
{
    private iterable $entries;
    private string $id;

    public function __construct(RecurringScheduleEntry ...$entries)
    {
        $this->entries = new ArrayCollection($entries);
    }

    public function firstAfter(\DateTimeInterface $dateTime): \DateTimeImmutable
    {
        $first = null;
        foreach ($this->entries as $entry) {
            $candidate = $entry->firstAfter($dateTime);
            if (null === $first || $candidate < $first) {
                $first = $candidate;
            }
        }

        return $first;
    }

    public function toArray(): array
    {
        return array_merge(...array_map(fn (RecurringScheduleEntry $entry) => $entry->toArray(), iterator_to_array($this->entries)));
    }
}
