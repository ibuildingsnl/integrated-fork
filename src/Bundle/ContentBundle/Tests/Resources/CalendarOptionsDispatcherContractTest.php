<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class CalendarOptionsDispatcherContractTest extends TestCase
{
    public function testCalendarOptionsUsesKernelEventDispatcher(): void
    {
        $services = file_get_contents(__DIR__.'/../../Resources/config/services.xml');

        self::assertIsString($services);
        self::assertStringContainsString('<service id="Integrated\\Bundle\\ContentBundle\\Services\\CalendarOptions">', $services);
        self::assertStringContainsString('<argument type="service" id="event_dispatcher"/>', $services);
        self::assertStringNotContainsString('<argument type="service" id="integrated_content.form.factory.event_dispatcher"/>', $services);
    }

    public function testCalendarPublicationSubscriberUsesKernelSubscriberTag(): void
    {
        $listeners = file_get_contents(__DIR__.'/../../Resources/config/event_listeners.xml');

        self::assertIsString($listeners);
        self::assertStringContainsString('<service id="integrated_content.event_listener.calendar_publication"', $listeners);
        self::assertStringContainsString('<tag name="kernel.event_subscriber" />', $listeners);
        self::assertStringNotContainsString('<tag name="integrated_content.form.event_subscriber" />', $listeners);
    }
}
