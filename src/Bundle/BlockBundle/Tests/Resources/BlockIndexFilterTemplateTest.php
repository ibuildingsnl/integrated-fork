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
        $formType = file_get_contents(__DIR__.'/../../Form/Type/BlockFilterType.php');

        self::assertIsString($template);
        self::assertIsString($formType);
        self::assertStringContainsString('function bindFilterQueryAutoSubmit()', $template);
        self::assertStringContainsString('input[name="integrated_block_filter[q]"]', $template);
        self::assertStringContainsString('window.setTimeout(function () {', $template);
        self::assertStringContainsString('bindFilterQueryAutoSubmit();', $template);
        self::assertStringNotContainsString("add('submit', SubmitType::class", $formType);
        self::assertStringNotContainsString('SubmitType;', $formType);
    }
}
