<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class WebsiteChannelsBlocksDropdownTemplateTest extends TestCase
{
    public function testWebsiteDropdownTemplateContainsEditorBlocksDropdown(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/partials/block.websites.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% if showBlocks|default(false) %}', $template);
        self::assertStringContainsString('{% trans %}Blokken{% endtrans %}', $template);
        self::assertStringContainsString("path('integrated_block_block_edit', {'id': block.id})", $template);
        self::assertStringContainsString('{% trans %}Geen blokken op deze pagina{% endtrans %}', $template);
    }
}
