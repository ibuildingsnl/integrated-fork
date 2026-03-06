<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarGridTemplateNavigationGuardTest extends TestCase
{
    public function testTemplateDisablesContentLinksInsideEditableColumns(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-css.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString(
            '.integrated-website-col[data-block-type="column"] [data-block-type="block"] .integrated-block a[href]',
            $template
        );
        self::assertStringContainsString('pointer-events: none;', $template);
    }

    public function testGridSortableUsesFallbackOnBodyForCrossColumnDragging(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-grid-js.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('forceFallback: true,', $template);
        self::assertStringContainsString('fallbackOnBody: true,', $template);
    }
}
