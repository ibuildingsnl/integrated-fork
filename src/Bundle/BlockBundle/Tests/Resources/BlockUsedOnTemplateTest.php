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
        self::assertStringContainsString('{% set usedContainerBlocks = integrated_find_container_blocks(block) %}', $template);
        self::assertStringContainsString('{% set usedTemplateUsages = integrated_find_template_usages(block) %}', $template);
        self::assertStringContainsString('{% trans %}Code usage{% endtrans %}', $template);
        self::assertStringContainsString('{% trans %}Container blocks{% endtrans %}', $template);
        self::assertStringContainsString("path('integrated_page_page_edit', {'id': page['_id']})", $template);
        self::assertStringContainsString("path('integrated_block_block_edit', {'id': containerBlock['_id']})", $template);
        self::assertStringContainsString("{{ templateUsage.template|default('unknown template') }}", $template);
        self::assertStringContainsString('page.channel[\'$id\']|default(null)', $template);
        self::assertStringContainsString('page._used_via_container_title is defined', $template);
        self::assertStringContainsString("{{ page.title|default(page['_id']) }}", $template);
    }

    public function testBlockEditTemplateContainsUsedOnSectionWithPageLinks(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% trans %}Used on{% endtrans %}', $template);
        self::assertStringContainsString('{% set usedPages = integrated_find_pages(block) %}', $template);
        self::assertStringContainsString('{% set usedContainerBlocks = integrated_find_container_blocks(block) %}', $template);
        self::assertStringContainsString('{% set usedTemplateUsages = integrated_find_template_usages(block) %}', $template);
        self::assertStringContainsString('{% trans %}Code usage{% endtrans %}', $template);
        self::assertStringContainsString('{% trans %}Container blocks{% endtrans %}', $template);
        self::assertStringContainsString("path('integrated_page_page_edit', {'id': page['_id']})", $template);
        self::assertStringContainsString("path('integrated_block_block_edit', {'id': containerBlock['_id']})", $template);
        self::assertStringContainsString("{{ templateUsage.template|default('unknown template') }}", $template);
        self::assertStringContainsString('page.channel[\'$id\']|default(null)', $template);
        self::assertStringContainsString('page._used_via_container_title is defined', $template);
    }
}
