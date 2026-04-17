<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarMenuDirtyTrackingContractTest extends TestCase
{
    public function testToolbarMenuScriptExposesDirtyTrackingHooks(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-menu-js.html.twig');

        $this->assertIsString($script);
        $this->assertStringContainsString('var workspaceDirty = false;', $script);
        $this->assertStringContainsString("menuRoot.dataset.integratedMenuDirty = dirty ? '1' : '0';", $script);
        $this->assertStringContainsString('workspaceDirty = true;', $script);
        $this->assertStringContainsString('isDirty: function(element) {', $script);
        $this->assertStringContainsString('setMenuDirty(item, false);', $script);
    }
}
