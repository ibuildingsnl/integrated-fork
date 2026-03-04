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
        self::assertMatchesRegularExpression('/off\\("dragenter"\\+\\w+,"\\.media-library"\\)\\.on\\("dragenter"\\+\\w+,"\\.media-library"/', $compiled);
        self::assertMatchesRegularExpression('/off\\("dragover"\\+\\w+,"\\.media-library"\\)\\.on\\("dragover"\\+\\w+,"\\.media-library"/', $compiled);
        self::assertMatchesRegularExpression('/off\\("drop"\\+\\w+,"\\.media-library"\\)\\.on\\("drop"\\+\\w+,"\\.media-library"/', $compiled);
        self::assertStringContainsString('dataTransfer', $compiled);
        self::assertStringContainsString('toggle_upload_view()', $compiled);
    }
}
