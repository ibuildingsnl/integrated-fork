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

        $metadata = new class() {
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
