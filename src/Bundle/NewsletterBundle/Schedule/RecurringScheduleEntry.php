<?php

namespace Integrated\Bundle\NewsletterBundle\Schedule;

interface RecurringScheduleEntry
{
    public function firstAfter(\DateTimeInterface $dateTime): \DateTimeInterface;

    public function toArray(): array;
}
