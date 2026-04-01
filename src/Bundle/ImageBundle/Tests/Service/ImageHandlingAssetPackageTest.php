<?php

declare(strict_types=1);

namespace Integrated\Bundle\ImageBundle\Tests\Service;

use Integrated\Bundle\ImageBundle\Image\ImageHandler;
use Integrated\Bundle\ImageBundle\Service\ImageHandling;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Config\FileLocatorInterface;

final class ImageHandlingAssetPackageTest extends TestCase
{
    public function testCacheAndFilesUseUnversionedAssetPackage(): void
    {
        $calls = [];
        $packages = $this->createMock(Packages::class);
        $packages->expects(self::exactly(2))
            ->method('getUrl')
            ->willReturnCallback(static function (string $path, ?string $packageName = null) use (&$calls): string {
                $calls[] = [$path, $packageName];

                return $path;
            });

        $callback = $this->createFileCallback($packages);

        self::assertSame('/cache/example.jpg', $callback('/cache/example.jpg'));
        self::assertSame('/files/example.jpg', $callback('/files/example.jpg'));
        self::assertSame(
            [
                ['/cache/example.jpg', 'unversioned'],
                ['/files/example.jpg', 'unversioned'],
            ],
            $calls
        );
    }

    public function testBundleAssetsKeepDefaultAssetPackage(): void
    {
        $packages = $this->createMock(Packages::class);
        $packages->expects(self::once())
            ->method('getUrl')
            ->with('bundles/twindigitaltheme/app.css')
            ->willReturn('/bundles/twindigitaltheme/app.css?v=1.0.0');

        $callback = $this->createFileCallback($packages);

        self::assertSame('/bundles/twindigitaltheme/app.css?v=1.0.0', $callback('bundles/twindigitaltheme/app.css'));
    }

    private function createFileCallback(Packages $packages): callable
    {
        $handling = new ImageHandling(
            'cache',
            0777,
            __DIR__,
            ImageHandler::class,
            $packages,
            $this->createMock(FileLocatorInterface::class),
            false,
            ''
        );

        $handler = $handling->open('bundles/twindigitaltheme/app.css');
        $property = new \ReflectionProperty(ImageHandler::class, 'fileCallback');
        $property->setAccessible(true);

        $callback = $property->getValue($handler);
        self::assertIsCallable($callback);

        return $callback;
    }
}
