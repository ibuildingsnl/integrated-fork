<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Service;

use Integrated\Bundle\BlockBundle\Service\RuntimeBlockUsageCollector;
use PHPUnit\Framework\TestCase;

final class RuntimeBlockUsageCollectorTest extends TestCase
{
    public function testCollectorDeduplicatesByIdAndKeepsMetadata(): void
    {
        $collector = new RuntimeBlockUsageCollector();
        $collector->register(' footer_nl ');
        $collector->register('footer_nl', 'Footer', 'html');
        $collector->register('hero', 'Hero', 'hero');

        self::assertSame(
            [
                ['id' => 'footer_nl', 'title' => 'Footer', 'type' => 'html'],
                ['id' => 'hero', 'title' => 'Hero', 'type' => 'hero'],
            ],
            $collector->all()
        );
    }
}
