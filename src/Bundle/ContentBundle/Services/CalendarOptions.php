<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CalendarOptions
{
    private const DEFAULT_LIMIT = 100;
    private const MAX_LIMIT = 100;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function prepare(array $options): array
    {
        unset($options['_view']);
        switch ($options['view']) {
            case 'week':
                $options['_view'] = '_week';
                $options['week'] = empty($options['week']) ? 'monday this week' : $options['week'];
                $options['limit'] = $this->normalizeCalendarLimit($options['limit'] ?? null);
                $options['start'] = new \DateTimeImmutable($options['week']);
                // Summer/winter time fix, @todo better fix
                if ($options['start']->format('H') > 12) {
                    $options['start'] = $options['start']->modify('+1 day 0:00');
                } else {
                    $options['start'] = $options['start']->modify('0:00');
                }
                $options['end'] = $options['start']->add(\DateInterval::createFromDateString('1 week'));
                $options['sort'] = 'time';
                $options['order'] = 'asc';
                if ($this->dispatcher->hasListeners(CalendarEvent::PREPARED_WEEK_OPTIONS)) {
                    $options = $this->dispatcher->dispatch(new CalendarEvent($options), CalendarEvent::PREPARED_WEEK_OPTIONS)->options;
                }
                break;
            case 'month':
                $options['_view'] = '_month';
                $options['month'] = $options['month'] ?? 'first day of this month';
                $options['limit'] = $this->normalizeCalendarLimit($options['limit'] ?? null);
                $options['start'] = new \DateTimeImmutable($options['month']);
                $options['end'] = $options['start']->add(\DateInterval::createFromDateString('1 month'));
                $options['sort'] = 'time';
                $options['order'] = 'asc';
                if ($this->dispatcher->hasListeners(CalendarEvent::PREPARED_MONTH_OPTIONS)) {
                    $options = $this->dispatcher->dispatch(new CalendarEvent($options), CalendarEvent::PREPARED_MONTH_OPTIONS)->options;
                }
                break;
        }

        return $options;
    }

    private function normalizeCalendarLimit(mixed $value): int
    {
        if (is_int($value)) {
            return max(1, min(self::MAX_LIMIT, $value));
        }

        if (is_string($value) && ctype_digit($value)) {
            return max(1, min(self::MAX_LIMIT, (int) $value));
        }

        if (is_numeric($value)) {
            return max(1, min(self::MAX_LIMIT, (int) $value));
        }

        return self::DEFAULT_LIMIT;
    }
}
