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
    }
}
