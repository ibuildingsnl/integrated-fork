<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarPageJsContractTest extends TestCase
{
    public function testUsesPageBuilderSaveRouteAndPayloadKeys(): void
    {
        $path = __DIR__.'/../../Resources/views/themes/default/objects/toolbar-page-js.html.twig';
        $content = (string) file_get_contents($path);

        self::assertStringContainsString("integrated_website_pagebuilder_save", $content);
        self::assertStringContainsString("'layoutVersion': 2", $content);
        self::assertStringContainsString("'expectedRevision': currentPageRevision", $content);
        self::assertStringContainsString("'force': forceSave === true", $content);
        self::assertStringContainsString("'payload':", $content);
        self::assertStringContainsString("'type': 'container'", $content);
        self::assertStringContainsString("'type': 'block_ref'", $content);
        self::assertStringContainsString('getBlockCssClass', $content);
        self::assertStringContainsString('blockProps.cssClass = blockCssClass', $content);
        self::assertStringContainsString("closeEditorAfterSave = this.dataset.closeEditorOnSuccess === '1';", $content);
        self::assertStringContainsString('closeEditorAfterSave && target', $content);
        self::assertStringContainsString('pendingSaveRequests = 2', $content);
        self::assertStringContainsString("setSaveStatus('saving'", $content);
        self::assertStringContainsString("setSaveStatus('saved'", $content);
        self::assertStringContainsString("setSaveStatus('dirty'", $content);
        self::assertStringContainsString("setSaveStatus('error'", $content);
        self::assertStringContainsString('setSaveActionsEnabled(false);', $content);
        self::assertStringContainsString('setSaveActionsEnabled(isEditorDirty && pendingSaveRequests === 0);', $content);
        self::assertStringContainsString("button.classList.toggle('is-disabled', !isEnabled);", $content);
        self::assertStringContainsString("button.setAttribute('tabindex', isEnabled ? '0' : '-1');", $content);
        self::assertStringContainsString('result.status === 409', $content);
        self::assertStringContainsString('handleConflict(result, token);', $content);
        self::assertStringContainsString('beginSaveCycle(true);', $content);
        self::assertStringContainsString('setPageRevision(parseInt(result.data.currentRevision, 10));', $content);
        self::assertStringContainsString('integrated-editor-draft:v', $content);
        self::assertStringContainsString('window.localStorage.setItem', $content);
        self::assertStringContainsString('window.localStorage.getItem', $content);
        self::assertStringContainsString('initViewportPreview', $content);
        self::assertStringContainsString('applyEditorViewport', $content);
        self::assertStringContainsString('setViewportPreviewMode', $content);
        self::assertStringContainsString('integrated-editor-device-preview-frame', $content);
        self::assertStringContainsString('integrated-editor-viewport:v1:', $content);
        self::assertStringContainsString("data-action=\"integrated-editor-viewport-set\"", $content);
        self::assertStringContainsString("button.dataset.viewport", $content);
        self::assertStringContainsString('maybeRestoreDraft()', $content);
        self::assertStringContainsString('restoreDraftSnapshot(draft).then', $content);
        self::assertStringContainsString("document.addEventListener('integrated-editor-content-change'", $content);
        self::assertStringContainsString("window.addEventListener('beforeunload'", $content);
        self::assertStringContainsString('isEditableShortcutTarget', $content);
        self::assertStringContainsString('handleKeyboardShortcuts', $content);
        self::assertStringContainsString("var key = (e.key || '').toLowerCase();", $content);
        self::assertStringContainsString("key === 's'", $content);
        self::assertStringContainsString("triggerEditorAction('integrated-website-page-save'", $content);
        self::assertStringContainsString("triggerEditorAction('integrated-website-history-undo'", $content);
        self::assertStringContainsString("triggerEditorAction('integrated-website-history-redo'", $content);
        self::assertStringContainsString("document.addEventListener('keydown', function(e) {", $content);
        self::assertStringContainsString('handleKeyboardShortcuts(e);', $content);
        self::assertStringContainsString("window.confirm(unsavedNavigationMessage)", $content);
        self::assertStringContainsString('{% trans %}Unsaved changes{% endtrans %}', $content);
    }
}
