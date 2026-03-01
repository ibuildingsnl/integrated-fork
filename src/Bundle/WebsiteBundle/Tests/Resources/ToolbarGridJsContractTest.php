<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarGridJsContractTest extends TestCase
{
    public function testGridEditorScriptUsesSortableGuardAndThrottledHistorySnapshots(): void
    {
        $path = __DIR__.'/../../Resources/views/themes/default/objects/toolbar-grid-js.html.twig';
        $content = (string) file_get_contents($path);

        self::assertStringContainsString('__integratedSortableInstance', $content);
        self::assertStringContainsString('animation: 120', $content);
        self::assertStringContainsString('delayOnTouchOnly: true', $content);
        self::assertStringContainsString('rememberStateNow', $content);
        self::assertStringContainsString('flushRememberState', $content);
        self::assertStringContainsString("document.addEventListener('block-deleted'", $content);
        self::assertStringContainsString('$blockTarget = null;', $content);
        self::assertStringContainsString('return $blockButtons.parentNode;', $content);
        self::assertStringContainsString('isTailwindLayout', $content);
        self::assertStringContainsString('$grid.dataset.gridFramework', $content);
        self::assertStringContainsString('parsedWrapperClasses', $content);
        self::assertStringContainsString('$block.innerHTML = \'<div class="\' + parsedWrapperClasses.join(\' \') + \'">\' + data.html + \'</div>\';', $content);
        self::assertStringContainsString("document.dispatchEvent(new CustomEvent('integrated-editor-content-change'", $content);
        self::assertStringContainsString("sortableActiveClass = 'integrated-sortable-active'", $content);
        self::assertStringContainsString('setSortableActive(true);', $content);
        self::assertStringContainsString('setSortableActive(false);', $content);
        self::assertStringContainsString('refreshBlock(item, true, false)', $content);
        self::assertStringContainsString('window.Integrated = Object.assign', $content);
        self::assertStringContainsString('Grid: {', $content);
        self::assertStringContainsString('serializeState: function()', $content);
        self::assertStringContainsString('restoreState: function(serializedState)', $content);
    }
}
