<?php

namespace Integrated\Bundle\ContentBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CalendarEvent extends Event
{
    public const PREPARED_WEEK_OPTIONS = 'calendar.options.week.prepared';
    public const PREPARED_MONTH_OPTIONS = 'calendar.options.month.prepared';

    public function __construct(
        public array $options,
    ) {
    }
}
