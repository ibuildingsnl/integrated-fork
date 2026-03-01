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
        self::assertStringContainsString('data-revision="{{ pageRevision }}"', $content);
        self::assertStringContainsString("isEditorPreview = app.request.query.get('integrated_editor_preview')", $content);
        self::assertStringContainsString('{% if not isEditorPreview %}', $content);
        self::assertStringContainsString('data-role="integrated-editor-viewport-controls"', $content);
        self::assertStringContainsString('data-action="integrated-editor-viewport-set"', $content);
        self::assertStringContainsString('data-viewport="desktop"', $content);
        self::assertStringContainsString('data-viewport="tablet"', $content);
        self::assertStringContainsString('data-viewport="mobile"', $content);
        self::assertStringContainsString('iconoir-computer', $content);
        self::assertStringContainsString('iconoir-laptop', $content);
        self::assertStringContainsString('iconoir-smartphone-device', $content);
        self::assertStringContainsString('title="{% trans %}Undo (Ctrl/Cmd+Z){% endtrans %}"', $content);
        self::assertStringContainsString('title="{% trans %}Redo (Ctrl/Cmd+Shift+Z){% endtrans %}"', $content);
        self::assertStringContainsString('role="status" aria-live="polite" aria-atomic="true"', $content);
        self::assertStringContainsString('aria-label="{% trans %}Close editor{% endtrans %}"', $content);
        self::assertStringContainsString('{% trans %}Save and close{% endtrans %}', $content);
        self::assertStringContainsString('collapseToolbarDropdowns', $content);
        self::assertStringContainsString("el.setAttribute('aria-expanded', 'false');", $content);
        self::assertStringContainsString("el.setAttribute('aria-haspopup', 'true');", $content);
        self::assertStringContainsString("document.dispatchEvent(new CustomEvent('integrated-editor-ready'));", $content);

        $editorCommandbarPos = strpos($content, 'class="integrated-editor-commandbar"');
        $toolbarRightPos = strpos($content, 'class="integrated-website-toolbar-right"');
        self::assertIsInt($editorCommandbarPos);
        self::assertIsInt($toolbarRightPos);
        self::assertLessThan($toolbarRightPos, $editorCommandbarPos);
    }
}
