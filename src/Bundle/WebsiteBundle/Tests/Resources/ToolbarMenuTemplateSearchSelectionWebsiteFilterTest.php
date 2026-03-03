<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarMenuTemplateSearchSelectionWebsiteFilterTest extends TestCase
{
    public function testTemplateContainsWebsiteAwareSearchSelectionControls(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-menu-js.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('integrated-search-selection-website-label', $template);
        self::assertStringContainsString('integrated-search-selection-website-control', $template);
        self::assertStringContainsString('renderSearchSelectionOptions', $template);
        self::assertStringContainsString('extractSelectionChannels', $template);
        self::assertStringContainsString('classifySelection', $template);
    }
}
