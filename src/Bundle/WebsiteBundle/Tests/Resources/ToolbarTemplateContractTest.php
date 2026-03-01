<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarTemplateContractTest extends TestCase
{
    public function testEditorToolbarContainsSaveStatusElement(): void
    {
        $path = __DIR__.'/../../Resources/views/toolbar.html.twig';
        $content = (string) file_get_contents($path);

        self::assertStringContainsString('data-role="integrated-website-save-status"', $content);
        self::assertStringContainsString('integrated-website-save-status', $content);
        self::assertStringContainsString('data-role="integrated-editor-commandbar"', $content);
        self::assertStringContainsString('integrated-view-commandbar', $content);
        self::assertStringContainsString('class="integrated-toolbar-websites" role="list"', $content);
        self::assertStringContainsString('integrated-toolbar-account-shell', $content);
        self::assertStringContainsString('data-close-editor-on-success="0"', $content);
        self::assertStringContainsString('data-close-editor-on-success="1"', $content);
        self::assertStringContainsString('{% trans %}Save and close{% endtrans %}', $content);
        self::assertStringContainsString('collapseToolbarDropdowns', $content);
        self::assertStringContainsString("el.setAttribute('aria-expanded', 'false');", $content);

        $editorCommandbarPos = strpos($content, 'class="integrated-editor-commandbar"');
        $toolbarRightPos = strpos($content, 'class="integrated-website-toolbar-right"');
        self::assertIsInt($editorCommandbarPos);
        self::assertIsInt($toolbarRightPos);
        self::assertLessThan($toolbarRightPos, $editorCommandbarPos);
    }
}
