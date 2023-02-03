<?php

namespace Integrated\Bundle\NewsletterBundle\Document\Schedule;

class SingleRecurringScheduleEntry implements RecurringScheduleEntry
{
    private string $id;
    public function __construct(
        private readonly array $baseModifiers,
        private readonly string $nextModifier,
        private readonly array $description,
    ) {
    }

    public function firstAfter(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        if ($dateTime instanceof \DateTime) {
            $dateTime = \DateTimeImmutable::createFromMutable($dateTime);
        }
        if (!$dateTime instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException();
        }
        $next = $this->applyBaseModifiers($dateTime);
        while ($next < $dateTime) {
            $next = $this->applyBaseModifiers($next->modify($this->nextModifier));
        }
        return $next;
    }

    public function toArray(): array
    {
        return $this->description;
    }

    private function applyBaseModifiers(\DateTimeImmutable $dateTime): \DateTimeImmutable
    {
        foreach ($this->baseModifiers as $modifier) {
            $dateTime = $dateTime->modify($modifier);
        }
        return $dateTime;
    }
}
