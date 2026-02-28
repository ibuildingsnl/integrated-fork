<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Integrated\Bundle\ContentBundle\Services\CalendarOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CalendarOptionsTest extends TestCase
{
    public function testWeekViewUsesSafeDefaultLimit(): void
    {
        $service = new CalendarOptions(new EventDispatcher());

        $options = $service->prepare([
            'view' => 'week',
            'week' => 'monday this week',
        ]);

        self::assertSame(100, $options['limit']);
        self::assertSame('time', $options['sort']);
        self::assertSame('asc', $options['order']);
        self::assertSame('_week', $options['_view']);
        self::assertInstanceOf(\DateTimeImmutable::class, $options['start']);
        self::assertInstanceOf(\DateTimeImmutable::class, $options['end']);
    }

    /**
     * @dataProvider provideCappedLimits
     */
    public function testWeekViewCapsAndNormalizesLimit(mixed $rawLimit, int $expected): void
    {
        $service = new CalendarOptions(new EventDispatcher());

        $options = $service->prepare([
            'view' => 'week',
            'week' => 'monday this week',
            'limit' => $rawLimit,
        ]);

        self::assertSame($expected, $options['limit']);
    }

    /**
     * @dataProvider provideCappedLimits
     */
    public function testMonthViewCapsAndNormalizesLimit(mixed $rawLimit, int $expected): void
    {
        $service = new CalendarOptions(new EventDispatcher());

        $options = $service->prepare([
            'view' => 'month',
            'month' => 'first day of this month',
            'limit' => $rawLimit,
        ]);

        self::assertSame($expected, $options['limit']);
        self::assertSame('_month', $options['_view']);
    }

    /**
     * @return iterable<string, array{0:mixed, 1:int}>
     */
    public function provideCappedLimits(): iterable
    {
        yield 'lower string limit' => ['25', 25];
        yield 'lower int limit' => [25, 25];
        yield 'zero limit defaults to minimum' => [0, 1];
        yield 'negative limit defaults to minimum' => [-10, 1];
        yield 'max int cap' => [9999, 100];
        yield 'max string cap' => ['9999', 100];
        yield 'non numeric defaults' => ['foo', 100];
        yield 'null defaults' => [null, 100];
    }
}
