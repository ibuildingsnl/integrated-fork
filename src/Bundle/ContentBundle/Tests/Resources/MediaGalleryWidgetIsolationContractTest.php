<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class MediaGalleryWidgetIsolationContractTest extends TestCase
{
    public function testMediaWidgetSelectionIsScopedToTheActiveIframeOnly(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/mediagallery_selection.js');

        self::assertIsString($source);
        self::assertStringContainsString('activeIframe.contentWindow !== e.source', $source);
        self::assertStringNotContainsString('IntegratedMediaLibrarySession', $source);
        self::assertStringNotContainsString('lastBrowseUrlByKey', $source);
    }

    public function testTinyMceDialogKeepsDedicatedWindowManagerFlow(): void
    {
        $source = file_get_contents(__DIR__.'/../../../FormTypeBundle/Resources/assets/js/tinymce-integrated-browser/ui/dialog.js');

        self::assertIsString($source);
        self::assertStringNotContainsString('openSharedMediaLibrary', $source);
        self::assertStringContainsString('editor.windowManager.openUrl({', $source);
    }
}
