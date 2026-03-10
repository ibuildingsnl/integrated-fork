<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Twig\Component\Admin;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\StatusBadge;
use PHPUnit\Framework\TestCase;

final class StatusBadgeComponentTest extends TestCase
{
    public function testUsesInactiveVariantByDefault(): void
    {
        $component = new StatusBadge();
        $component->label = 'Draft';

        self::assertSame('inactive', $component->variant);
        self::assertSame('status-inactive', $component->statusClass());
    }

    public function testMapsSentVariantToIntegratedClass(): void
    {
        $component = new StatusBadge();
        $component->label = 'Sent';
        $component->variant = 'sent';

        self::assertSame('status-sent', $component->statusClass());
    }

    public function testFallsBackToInactiveClassForUnknownVariant(): void
    {
        $component = new StatusBadge();
        $component->label = 'Unknown';
        $component->variant = 'something-else';

        self::assertSame('status-inactive', $component->statusClass());
    }
}
