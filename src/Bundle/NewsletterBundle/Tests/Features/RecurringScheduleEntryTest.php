<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\NewsletterBundle\Document\Schedule\CombinedRecurringScheduleEntry;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\ScheduleEntryFactory;
use PHPUnit\Framework\TestCase;

class RecurringScheduleEntryTest extends TestCase
{
    private ScheduleEntryFactory $schedule;

    protected function setUp(): void
    {
        $this->schedule = new ScheduleEntryFactory();
    }

    public function testScheduleDailyAtFive()
    {
        $entry = $this->schedule->daily(17, 00);

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('2-1-2023 17:00'), $nextOccurrence);
    }

    public function testScheduleDailyAtSeven()
    {
        $entry = $this->schedule->daily(19, 00);

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('1-1-2023 19:00'), $nextOccurrence);
    }

    public function testScheduleDailyAtTenThirty()
    {
        $entry = $this->schedule->daily(10, 30);

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('2-1-2023 10:30'), $nextOccurrence);
    }

    public function testScheduleDailyNow()
    {
        $entry = $this->schedule->daily(19, 00);

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 19:00'));

        self::assertEquals(new \DateTime('1-1-2023 19:00'), $nextOccurrence);
    }

    public function testScheduleDailyToArray()
    {
        $entry = $this->schedule->daily(10, 30);

        $details = $entry->toArray();

        self::assertEquals([[
            'frequency' => 'daily',
            'hour' => 10,
            'minute' => 30,
        ]], $details);
    }

    public function testScheduleDailyFromArray()
    {
        $hour = random_int(0, 23);
        $minute = random_int(0, 59);

        self::assertEquals(
            $this->schedule->daily($hour, $minute),
            $this->schedule->fromArray([[
                'frequency' => 'daily',
                'hour' => $hour,
                'minute' => $minute,
            ]])
        );
    }

    public function testScheduleTwiceDailyFromArray()
    {
        $hour1 = random_int(0, 23);
        $minute1 = random_int(0, 59);
        $hour2 = random_int(0, 23);
        $minute2 = random_int(0, 59);

        self::assertEquals(
            new CombinedRecurringScheduleEntry(
                $this->schedule->daily($hour1, $minute1),
                $this->schedule->daily($hour2, $minute2),
            ),
            $this->schedule->fromArray([
                [
                    'frequency' => 'daily',
                    'hour' => $hour1,
                    'minute' => $minute1,
                ],
                [
                    'frequency' => 'daily',
                    'hour' => $hour2,
                    'minute' => $minute2,
                ],
            ])
        );
    }

    public function testScheduleThursdaysAtFive()
    {
        $entry = $this->schedule->weekly(17, 00, 'thursday');

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('5-1-2023 17:00'), $nextOccurrence);
    }

    public function testScheduleWednesdaysAtFiveIn2020()
    {
        $entry = $this->schedule->weekly(17, 00, 'wednesday');

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2020 18:00'));

        self::assertEquals(new \DateTime('8-1-2020 17:00'), $nextOccurrence);
    }

    public function testScheduleFridaysNow()
    {
        $entry = $this->schedule->weekly(17, 00, 'friday');

        $nextOccurrence = $entry->firstAfter(new \DateTime('6-1-2023 17:00'));

        self::assertEquals(new \DateTime('6-1-2023 17:00'), $nextOccurrence);
    }

    public function testScheduleSundaysToArray()
    {
        $entry = $this->schedule->weekly(10, 30, 'sunday');

        $details = $entry->toArray();

        self::assertEquals([[
            'frequency' => 'weekly',
            'hour' => 10,
            'minute' => 30,
            'weekday' => 'sunday',
        ]], $details);
    }

    public function testScheduleMondaysFromArray()
    {
        $hour = random_int(0, 23);
        $minute = random_int(0, 59);

        self::assertEquals(
            $this->schedule->weekly($hour, $minute, 'monday'),
            $this->schedule->fromArray([[
                'frequency' => 'weekly',
                'weekday' => 'monday',
                'hour' => $hour,
                'minute' => $minute,
            ]]),
        );
    }

    public function testScheduleMonthlyAtFour()
    {
        $entry = $this->schedule->monthly(16, 00, 1);

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('1-2-2023 16:00'), $nextOccurrence);
    }

    public function testScheduleTheFifthOfTheMonthAtFour()
    {
        $entry = $this->schedule->monthly(16, 00, 5);

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('5-1-2023 16:00'), $nextOccurrence);
    }

    public function testScheduleTheFirstSaturdayOfTheMonthAtFour()
    {
        $entry = $this->schedule->monthly(16, 00, 1, 'saturday');

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('7-1-2023 16:00'), $nextOccurrence);
    }

    public function testScheduleTheSecondSaturdayOfTheMonthAtFour()
    {
        $entry = $this->schedule->monthly(16, 00, 2, 'saturday');

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('14-1-2023 16:00'), $nextOccurrence);
    }

    public function testScheduleTheFourthSaturdayOfTheMonthAtSix()
    {
        $entry = $this->schedule->monthly(18, 00, 4, 'saturday');

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 10:00'));

        self::assertEquals(new \DateTime('28-1-2023 18:00'), $nextOccurrence);
    }

    public function testScheduleTheThirdSundayOfTheMonthAtMidnight()
    {
        $entry = $this->schedule->monthly(00, 00, 3, 'sunday');

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 05:12'));

        self::assertEquals(new \DateTime('15-1-2023 00:00'), $nextOccurrence);
    }

    public function testScheduleTheFifthOfTheMonthNow()
    {
        $entry = $this->schedule->monthly(16, 00, 5);

        $nextOccurrence = $entry->firstAfter(new \DateTime('5-1-2023 16:00'));

        self::assertEquals(new \DateTime('5-1-2023 16:00'), $nextOccurrence);
    }

    public function testScheduleMonthlyToArray()
    {
        $entry = $this->schedule->monthly(12, 55, 12);

        $details = $entry->toArray();

        self::assertEquals([[
            'frequency' => 'monthly',
            'weekday' => 'day',
            'hour' => 12,
            'minute' => 55,
            'day' => 12,
        ]], $details);
    }

    public function testScheduleMonthlyWeekdayToArray()
    {
        $entry = $this->schedule->monthly(12, 55, 2, 'monday');

        $details = $entry->toArray();

        self::assertEquals([[
            'frequency' => 'monthly',
            'weekday' => 'monday',
            'hour' => 12,
            'minute' => 55,
            'day' => 2,
        ]], $details);
    }

    public function testScheduleMonthlyFromArray()
    {
        $hour = random_int(0, 23);
        $minute = random_int(0, 59);
        $day = random_int(1, 28);

        self::assertEquals(
            $this->schedule->monthly($hour, $minute, $day),
            $this->schedule->fromArray([[
                'frequency' => 'monthly',
                'day' => $day,
                'hour' => $hour,
                'minute' => $minute,
            ]]),
        );
    }

    public function testScheduleFirstOfMayAtFivePastOne()
    {
        $entry = $this->schedule->yearly(13, 05, 'may', 1);

        $nextOccurrence = $entry->firstAfter(new \DateTime('1-1-2023 18:00'));

        self::assertEquals(new \DateTime('1-5-2023 13:05'), $nextOccurrence);
    }

    public function testScheduleYearlyToArray()
    {
        $entry = $this->schedule->yearly(12, 25,'february', 6);

        $details = $entry->toArray();

        self::assertEquals([[
            'frequency' => 'yearly',
            'month' => 'february',
            'day' => 6,
            'hour' => 12,
            'minute' => 25,
        ]], $details);
    }

    public function testScheduleYearlyFromArray()
    {
        $hour = random_int(0, 23);
        $minute = random_int(0, 59);
        $day = random_int(1, 28);

        self::assertEquals(
            $this->schedule->yearly($hour, $minute, 'november', $day),
            $this->schedule->fromArray([[
                'frequency' => 'yearly',
                'month' => 'november',
                'day' => $day,
                'hour' => $hour,
                'minute' => $minute,
            ]]),
        );
    }

    public function testScheduleTwiceYearlyFromArray()
    {
        $hour1 = random_int(0, 23);
        $minute1 = random_int(0, 59);
        $day1 = random_int(1, 28);
        $hour2 = random_int(0, 23);
        $minute2 = random_int(0, 59);
        $day2 = random_int(1, 28);

        self::assertEquals(
            new CombinedRecurringScheduleEntry(
                $this->schedule->yearly($hour1, $minute1, 'july', $day1),
                $this->schedule->yearly($hour2, $minute2, 'december', $day2),
            ),
            $this->schedule->fromArray([
                [
                    'frequency' => 'yearly',
                    'month' => 'july',
                    'day' => $day1,
                    'hour' => $hour1,
                    'minute' => $minute1,
                ],
                [
                    'frequency' => 'yearly',
                    'month' => 'december',
                    'day' => $day2,
                    'hour' => $hour2,
                    'minute' => $minute2,
                ],
            ]),
        );
    }

    public function testScheduleQuarterlyQ1AtMidnight()
    {
        $entry = $this->schedule->quarterly(00, 00, 1);

        $nextOccurrence = $entry->firstAfter(new \DateTime('5-1-2023 18:00'));

        self::assertEquals(new \DateTime('1-3-2023 00:00'), $nextOccurrence);
    }

    public function testScheduleQuarterlyQ2AtMidnight()
    {
        $entry = $this->schedule->quarterly(00, 00, 1);

        $nextOccurrence = $entry->firstAfter(new \DateTime('5-4-2023 13:22'));

        self::assertEquals(new \DateTime('1-6-2023 00:00'), $nextOccurrence);
    }

    public function testScheduleQuarterlyQ3AtQuarterPastEight()
    {
        $entry = $this->schedule->quarterly(8, 15, 5);

        $nextOccurrence = $entry->firstAfter(new \DateTime('5-7-2023 13:22'));

        self::assertEquals(new \DateTime('5-9-2023 08:15'), $nextOccurrence);
    }

    public function testScheduleQuarterlyToArray()
    {
        $entry = $this->schedule->quarterly(14, 39,3);

        $details = $entry->toArray();

        self::assertEquals([
            [
                'frequency' => 'yearly',
                'month' => 'january',
                'day' => 3,
                'hour' => 14,
                'minute' => 39,
            ],
            [
                'frequency' => 'yearly',
                'month' => 'march',
                'day' => 3,
                'hour' => 14,
                'minute' => 39,
            ],
            [
                'frequency' => 'yearly',
                'month' => 'june',
                'day' => 3,
                'hour' => 14,
                'minute' => 39,
            ],
            [
                'frequency' => 'yearly',
                'month' => 'september',
                'day' => 3,
                'hour' => 14,
                'minute' => 39,
            ],
        ], $details);
    }

    public function testScheduleQuarterlyFromArray()
    {
        $hour = random_int(0, 23);
        $minute = random_int(0, 59);
        $day = random_int(1, 28);

        self::assertEquals(
            $this->schedule->quarterly($hour, $minute, $day),
            $this->schedule->fromArray([[
                'frequency' => 'quarterly',
                'day' => $day,
                'hour' => $hour,
                'minute' => $minute,
            ]]),
        );
    }
}
