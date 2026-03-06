<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockUsedOnTemplateTest extends TestCase
{
    public function testBlockListTemplateShowsClickableUsedOnLinks(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/partials/block_list.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% set usedPages = integrated_find_pages(block) %}', $template);
        self::assertStringContainsString("path('integrated_page_page_edit', {'id': page['_id']})", $template);
        self::assertStringContainsString('page.channel[\'$id\']|default(null)', $template);
        self::assertStringContainsString("{{ page.title|default(page['_id']) }}", $template);
    }

    public function testBlockEditTemplateContainsUsedOnSectionWithPageLinks(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% trans %}Used on{% endtrans %}', $template);
        self::assertStringContainsString('{% set usedPages = integrated_find_pages(block) %}', $template);
        self::assertStringContainsString("path('integrated_page_page_edit', {'id': page['_id']})", $template);
        self::assertStringContainsString('page.channel[\'$id\']|default(null)', $template);
    }
}
