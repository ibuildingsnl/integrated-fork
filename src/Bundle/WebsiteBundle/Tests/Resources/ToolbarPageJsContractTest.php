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
        self::assertStringContainsString("document.addEventListener('integrated-editor-content-change'", $content);
        self::assertStringContainsString("window.addEventListener('beforeunload'", $content);
        self::assertStringContainsString("window.confirm(unsavedNavigationMessage)", $content);
        self::assertStringContainsString('{% trans %}Unsaved changes{% endtrans %}', $content);
    }
}
