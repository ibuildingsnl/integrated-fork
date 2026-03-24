<?php

namespace Integrated\Bundle\ImageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PdfPreviewSupportContractTest extends TestCase
{
    public function testPdfConversionUsesDedicatedFirstPagePreviewFlow(): void
    {
        $adapter = file_get_contents(__DIR__.'/../../Converter/Adapter/ImageMagickAdapter.php');

        self::assertIsString($adapter);
        self::assertStringContainsString("getMetadata()->getExtension()", $adapter);
        self::assertStringContainsString("'pdf'", $adapter);
        self::assertStringContainsString("setResolution(", $adapter);
        self::assertStringContainsString("[0]", $adapter);
    }
}
