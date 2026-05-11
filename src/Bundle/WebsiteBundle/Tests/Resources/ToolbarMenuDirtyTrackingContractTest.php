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

    public function testToolbarMenuScriptExposesMenuPositionControls(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-menu-js.html.twig');
        $styles = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-css.html.twig');

        $this->assertIsString($script);
        $this->assertIsString($styles);
        $this->assertStringContainsString('integrated-menu-setting-parent', $script);
        $this->assertStringContainsString('integrated-menu-setting-order', $script);
        $this->assertStringContainsString('integrated-website-menu-item-move-up', $script);
        $this->assertStringContainsString('integrated-website-menu-item-move-down', $script);
        $this->assertStringContainsString('integrated-website-menu-item-move-under-previous', $script);
        $this->assertStringContainsString('integrated-website-menu-item-move-top', $script);
        $this->assertStringContainsString('moveNodeToParent(parentNode, parentChange.value || \'\');', $script);
        $this->assertStringContainsString('moveNodeToOrder(orderNode, orderChange.value);', $script);
        $this->assertStringContainsString('workspaceDirty = true;', $script);
        $this->assertStringContainsString('.integrated-menu-node-move-row', $styles);
        $this->assertStringContainsString('.integrated-menu-node-link-action', $styles);
    }
}
