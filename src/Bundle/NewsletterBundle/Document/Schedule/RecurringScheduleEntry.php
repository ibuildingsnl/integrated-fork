<?php

namespace Integrated\Bundle\NewsletterBundle\Document\Schedule;

interface RecurringScheduleEntry
{
    public function firstAfter(\DateTimeInterface $dateTime): \DateTimeImmutable;

    public function toArray(): array;
}
