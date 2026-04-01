<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class FacetBlockTemplateContractTest extends TestCase
{
    public function testFacetTemplateSupportsSingleSelectionReplacement(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/themes/default/blocks/facet/default.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("{% set isSingleSelect = 'single' == block.getSelectionMode() %}", $template);
        self::assertStringContainsString("{% set activeFilters = activeFilters|slice(0, 1) %}", $template);
        self::assertStringContainsString("{% set newParam = {(facetName): [name]} %}", $template);
    }
}
