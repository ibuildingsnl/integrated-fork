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

    public function testBlockIndexTemplateAutoSubmitsSearchWithoutDedicatedSubmitField(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/index.html.twig');
        $script = file_get_contents(__DIR__.'/../../Resources/public/js/block_index.js');
        $formType = file_get_contents(__DIR__.'/../../Form/Type/BlockFilterType.php');

        self::assertIsString($template);
        self::assertIsString($script);
        self::assertIsString($formType);
        self::assertStringContainsString('bundles/integratedblock/js/block_index.js', $template);
        self::assertStringContainsString('function bindFilterQueryAutoSubmit()', $script);
        self::assertStringContainsString('function bindFilterFormAutoSubmit()', $script);
        self::assertStringContainsString('input[name="integrated_block_filter[q]"]', $script);
        self::assertStringContainsString("document.addEventListener('change'", $script);
        self::assertStringContainsString("target.closest('form[name=\"integrated_block_filter\"]')", $script);
        self::assertStringContainsString('window.setTimeout(function () {', $script);
        self::assertStringContainsString('window.__integratedBlockIndexInteractionsBound', $script);
        self::assertStringNotContainsString("form.addEventListener('change'", $script);
        self::assertStringNotContainsString("add('submit', SubmitType::class", $formType);
        self::assertStringNotContainsString('SubmitType;', $formType);
    }
}
