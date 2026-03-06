<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class SearchSelectionIndexTemplateTest extends TestCase
{
    public function testIndexTemplateShowsSortAndOrderColumnsIncludingCustomSortHandling(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/search_selection/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% trans %}Sort by{% endtrans %}', $template);
        self::assertStringContainsString('{% trans %}Sort order{% endtrans %}', $template);
        self::assertStringContainsString("sortField starts with('custom:')", $template);
        self::assertStringContainsString('{% trans %}Custom sorting{% endtrans %}', $template);
    }
}
