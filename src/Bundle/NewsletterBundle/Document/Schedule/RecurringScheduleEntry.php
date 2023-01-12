<?php

namespace Integrated\Bundle\NewsletterBundle\Document\Schedule;

interface RecurringScheduleEntry
{
    public function firstAfter(\DateTimeInterface $dateTime): \DateTimeInterface;

    public function toArray(): array;
}
