<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class MediaGalleryDragToUploadFlowTest extends TestCase
{
    public function testSourceBindsFileDragToUploadView(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/mediaGallery.js');

        self::assertIsString($source);
        self::assertStringContainsString("'dragenter' + MEDIA_GALLERY_NS, '.media-library'", $source);
        self::assertStringContainsString('const hasFilesInDragEvent', $source);
        self::assertStringContainsString('toggle_upload_view();', $source);
    }

    public function testCompiledAssetContainsFileDragUploadBinding(): void
    {
        $compiled = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/public/mediagallery.js');

        self::assertIsString($compiled);
        self::assertStringContainsString("doc.off('dragenter' + MEDIA_GALLERY_NS, '.media-library')", $compiled);
        self::assertStringContainsString('var hasFilesInDragEvent = function hasFilesInDragEvent(event)', $compiled);
    }
}
