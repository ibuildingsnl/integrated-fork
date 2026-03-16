<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockIndexFilterTemplateTest extends TestCase
{
    public function testBlockIndexTemplateContainsUnusedFilterOption(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('facetFilter.unused', $template);
    }
}
