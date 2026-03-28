<?php

namespace Integrated\Bundle\ImageBundle\Tests\Twig\Extension;

use Integrated\Bundle\ImageBundle\Converter\WebFormatConverter;
use Integrated\Bundle\ImageBundle\Image\ImageHandler;
use Integrated\Bundle\ImageBundle\Service\ImageHandling;
use Integrated\Bundle\ImageBundle\Twig\Extension\GregwarImageExtension;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use PHPUnit\Framework\TestCase;

final class ImageExtensionPdfFallbackTest extends TestCase
{
    public function testStringPdfPathUsesGeneratedPreviewWhenGhostscriptIsAvailable(): void
    {
        if (!\extension_loaded('imagick') || !is_file('/usr/bin/gs')) {
            self::markTestSkipped('Imagick with Ghostscript is required for PDF preview generation.');
        }

        $pdfPath = sys_get_temp_dir().'/image-extension-preview-test.pdf';
        file_put_contents($pdfPath, <<<'PDF'
%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] /Contents 4 0 R /Resources << >> >>
endobj
4 0 obj
<< /Length 44 >>
stream
0.9 g
0 0 200 200 re
f
BT
/F1 12 Tf
20 100 Td
(PDF) Tj
ET
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000010 00000 n 
0000000063 00000 n 
0000000122 00000 n 
0000000220 00000 n 
trailer
<< /Size 5 /Root 1 0 R >>
startxref
311
%%EOF
PDF);

        $imageHandler = $this->createMock(ImageHandler::class);
        $imageHandling = $this->createMock(ImageHandling::class);
        $imageHandling
            ->expects(self::once())
            ->method('open')
            ->with(self::callback(static fn (string $path): bool => str_contains($path, 'cache/pdf-preview/') && str_ends_with($path, '.jpg')))
            ->willReturn($imageHandler);

        $extension = new ImageExtension(
            $imageHandling,
            $this->createMock(GregwarImageExtension::class),
            $this->createMock(WebFormatConverter::class),
            [],
            $this->createMock(ImageHandling::class)
        );

        try {
            self::assertSame($imageHandler, $extension->image($pdfPath));
        } finally {
            @unlink($pdfPath);
        }
    }

    public function testStoragePdfFallsBackToDedicatedPdfPlaceholderWhenConversionFails(): void
    {
        $imageHandler = $this->createMock(ImageHandler::class);
        $imageHandling = $this->createMock(ImageHandling::class);
        $imageHandling
            ->expects(self::once())
            ->method('open')
            ->with(self::callback(static fn (string $path): bool => str_contains($path, 'pdf-fallback.jpg')))
            ->willReturn($imageHandler);

        $webFormatConverter = $this->createMock(WebFormatConverter::class);
        $webFormatConverter
            ->expects(self::once())
            ->method('convert')
            ->willThrowException(new \RuntimeException('conversion failed'));

        $metadata = new class {
            public function getExtension(): string
            {
                return 'pdf';
            }
        };

        $storage = $this->createMock(StorageInterface::class);
        $storage
            ->method('getMetadata')
            ->willReturn($metadata);
        $storage
            ->method('getIdentifier')
            ->willReturn('opaque-storage-id-without-extension');

        $extension = new ImageExtension(
            $imageHandling,
            $this->createMock(GregwarImageExtension::class),
            $webFormatConverter,
            [],
            $this->createMock(ImageHandling::class)
        );

        self::assertSame($imageHandler, $extension->image($storage));
    }
}
