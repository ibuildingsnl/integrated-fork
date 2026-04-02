<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class WebsiteToolbarBlocksDropdownContractTest extends TestCase
{
    public function testToolbarPassesRuntimeBlockDataToWebsiteNavigationController(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/toolbar.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("'showBlocks': isEditorMode", $template);
        self::assertStringContainsString("'usedBlocks': usedBlocks|default([])", $template);
        self::assertStringContainsString("ChannelController::getChannels", $template);
    }
}
