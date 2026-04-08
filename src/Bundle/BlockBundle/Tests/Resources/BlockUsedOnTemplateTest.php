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
        self::assertStringContainsString("path('integrated_block_block_duplicate', duplicateRouteParams)", $template);
        self::assertStringContainsString("duplicateRouteParams = duplicateRouteParams|merge({'target_channel': duplicateTargetChannel})", $template);
        self::assertStringContainsString('{% trans %}Duplicate{% endtrans %}', $template);
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

    public function testDuplicateTemplateExtendsNewFormAndShowsSourceBlockInfo(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/duplicate.html.twig');
        $newTemplate = file_get_contents(__DIR__.'/../../Resources/views/block/new.html.twig');

        self::assertIsString($template);
        self::assertIsString($newTemplate);
        self::assertStringContainsString("{% extends '@IntegratedBlock/block/new.html.twig' %}", $template);
        self::assertStringContainsString('{% trans %}Duplicate block{% endtrans %}', $template);
        self::assertStringContainsString('{{ sourceBlock.id }}', $template);
        self::assertStringContainsString('data-duplicate-target-channel', $template);
        self::assertStringContainsString('data-id-availability-url', $template);
        self::assertStringContainsString('data-duplicate-id-status', $template);
        self::assertStringContainsString('scheduleAvailabilityCheck', $template);
        self::assertStringContainsString('checkIdAvailability', $template);
        self::assertStringContainsString('swapChannelInId', $template);
        self::assertStringContainsString('{% block editor_heading %}', $newTemplate);
        self::assertStringContainsString('{% block editor_intro %}', $newTemplate);
    }
}
