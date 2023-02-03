<?php

namespace Integrated\Bundle\NewsletterBundle\Document\Schedule;

class ScheduleEntryFactory
{
    public const DAILY = 'daily';
    public const WEEKLY = 'weekly';
    public const MONTHLY = 'monthly';
    public const QUARTERLY = 'quarterly';
    public const YEARLY = 'yearly';
    public const FREQUENCIES = [
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
        self::QUARTERLY,
        self::YEARLY,
    ];

    public function daily(int $hour, int $minute): RecurringScheduleEntry
    {
        return new SingleRecurringScheduleEntry(
            [sprintf('midnight + %d hours + %d minutes', $hour, $minute)],
            'tomorrow',
            [[
                'frequency' => self::DAILY,
                'hour' => $hour,
                'minute' => $minute,
            ]],
        );
    }

    public function weekly(int $hour, int $minute, string $day): RecurringScheduleEntry
    {
        return new SingleRecurringScheduleEntry(
            [sprintf('%s + %d hours + %d minutes', $day, $hour, $minute)],
            '+1 week',
            [[
                'frequency' => self::WEEKLY,
                'weekday' => $day,
                'hour' => $hour,
                'minute' => $minute,
            ]],
        );
    }

    public function monthly(int $hour, int $minute, int $nthDay, string $day = 'day'): RecurringScheduleEntry
    {
        return new SingleRecurringScheduleEntry(
            [
                sprintf('midnight first %s of this month', $day),
                sprintf('+ %d days + %d hours + %d minutes', ($day === 'day' ? $nthDay - 1 : ($nthDay - 1) * 7), $hour, $minute),
            ],
            'first day of next month',
            [[
                'frequency' => self::MONTHLY,
                'weekday' => $day,
                'day' => $nthDay,
                'hour' => $hour,
                'minute' => $minute,
            ]],
        );
    }

    public function yearly(int $hour, int $minute, string $month, int $nthDay): RecurringScheduleEntry
    {
        return new SingleRecurringScheduleEntry(
            [sprintf('midnight %s %d + %d hours + %d minutes', $month, $nthDay, $hour, $minute)],
            '+1 year',
            [[
                'frequency' => self::YEARLY,
                'month' => $month,
                'day' => $nthDay,
                'hour' => $hour,
                'minute' => $minute,
            ]],
        );
    }

    public function quarterly(int $hour, int $minute, int $nthDay): RecurringScheduleEntry
    {
        // Shortcut to adding 4 yearly entries
        return new CombinedRecurringScheduleEntry(
            $this->yearly($hour, $minute, 'january', $nthDay),
            $this->yearly($hour, $minute, 'march', $nthDay),
            $this->yearly($hour, $minute, 'june', $nthDay),
            $this->yearly($hour, $minute, 'september', $nthDay),
        );
    }

    public function fromArray(array $entryData)
    {
        $entries = array_map(fn(array $entry) => match ($entry['frequency']) {
            self::DAILY => $this->daily($entry['hour'], $entry['minute']),
            self::WEEKLY => $this->weekly($entry['hour'], $entry['minute'], $entry['weekday']),
            self::MONTHLY => $this->monthly($entry['hour'], $entry['minute'], $entry['day'], $entry['weekday'] ?? 'day'),
            self::QUARTERLY => $this->quarterly($entry['hour'], $entry['minute'], $entry['day']),
            self::YEARLY => $this->yearly($entry['hour'], $entry['minute'], $entry['month'], $entry['day']),
        }, $entryData);
        if (count($entries) === 1) {
            return $entries[0];
        }
        return new CombinedRecurringScheduleEntry(...$entries);
    }
}
