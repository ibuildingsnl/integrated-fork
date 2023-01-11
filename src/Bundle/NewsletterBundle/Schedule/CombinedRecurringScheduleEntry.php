<?php

namespace Integrated\Bundle\NewsletterBundle\Schedule;

class CombinedRecurringScheduleEntry implements RecurringScheduleEntry
{
    private readonly array $entries;

    public function __construct(RecurringScheduleEntry ...$entries)
    {
        $this->entries = $entries;
    }

    public function firstAfter(\DateTimeInterface $dateTime): \DateTimeInterface
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
        return array_merge(...array_map(fn(RecurringScheduleEntry $entry) => $entry->toArray(), $this->entries));
    }
}
