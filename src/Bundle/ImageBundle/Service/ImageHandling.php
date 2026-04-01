<?php

namespace Integrated\Bundle\ImageBundle\Service;

use Integrated\Bundle\ImageBundle\Image\ImageHandler;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Image manipulation service.
 *
 * @author Gregwar <g.passault@gmail.com>
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class ImageHandling
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var int
     */
    private $cacheDirMode;

    /**
     * @var string
     */
    private $webDirectory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Packages
     */
    private $assetsPackages;

    /**
     * @var string
     */
    private $handlerClass;

    /**
     * @var FileLocatorInterface|KernelInterface
     */
    private $fileLocator;

    /**
     * @var bool
     */
    private $throwException;

    private $fallbackImage;

    /**
     * @param string                               $cacheDirectory
     * @param int                                  $cacheDirMode
     * @param string                               $handlerClass
     * @param KernelInterface|FileLocatorInterface $fileLocator
     * @param bool                                 $throwException
     * @param string                               $fallbackImage
     */
    public function __construct($cacheDirectory, $cacheDirMode, $webDirectory, $handlerClass, Packages $assetsPackages, $fileLocator, $throwException, $fallbackImage)
    {
        if (!$fileLocator instanceof FileLocatorInterface && $fileLocator instanceof KernelInterface) {
            throw new \InvalidArgumentException(
                'Argument 5 passed to '.__METHOD__.' must be an instance of '.
                'Symfony\Component\Config\FileLocatorInterface or Symfony\Component\HttpKernel\KernelInterface.'
            );
        }

        if ($fileLocator instanceof KernelInterface) {
            @trigger_error(
                'Pass Symfony\Component\HttpKernel\KernelInterface to '.__CLASS__.
                ' is deprecated since version 2.1.0 and will be removed in 3.0.'.
                ' Use Symfony\Component\Config\FileLocatorInterface instead.',
                \E_USER_DEPRECATED
            );
        }

        $this->cacheDirectory = $cacheDirectory;
        $this->cacheDirMode = (int) $cacheDirMode;
        $this->webDirectory = $webDirectory;
        $this->handlerClass = $handlerClass;
        $this->assetsPackages = $assetsPackages;
        $this->fileLocator = $fileLocator;
        $this->throwException = $throwException;
        $this->fallbackImage = $fallbackImage;
    }

    /**
     * Get a manipulable image instance.
     *
     * @param string $file the image path
     *
     * @return ImageHandler a manipulable image instance
     */
    public function open($file)
    {
        if ($file !== '' && $file[0] == '@') {
            try {
                if ($this->fileLocator instanceof FileLocatorInterface) {
                    $file = $this->fileLocator->locate($file);
                } else {
                    $this->fileLocator->locateResource($file);
                }
            } catch (\InvalidArgumentException $exception) {
                if ($this->throwException || false == $this->fallbackImage) {
                    throw $exception;
                }

                $file = $this->fallbackImage;
            }
        }

        return $this->createInstance($file);
    }

    /**
     * Get a new image.
     *
     * @param string $w the width
     * @param string $h the height
     *
     * @return ImageHandler a manipulable image instance
     */
    public function create($w, $h)
    {
        return $this->createInstance(null, $w, $h);
    }

    /**
     * Creates an instance defining the cache directory.
     *
     * @param string      $file
     * @param string|null $w
     * @param string|null $h
     *
     * @return ImageHandler
     */
    private function createInstance($file, $w = null, $h = null)
    {
        $handlerClass = $this->handlerClass;
        /** @var ImageHandler $image */
        $image = new $handlerClass($file, $w, $h, $this->throwException, $this->fallbackImage);

        $image->setCacheDir($this->cacheDirectory);
        $cacheSystem = method_exists($image, 'getCacheSystem') ? $image->getCacheSystem() : null;
        if (\is_object($cacheSystem) && method_exists($cacheSystem, 'setDirectoryMode')) {
            $image->setCacheDirMode($this->cacheDirMode);
        }
        $image->setActualCacheDir($this->webDirectory.'/'.$this->cacheDirectory);

        $image->setFileCallback(function ($file) {
            if ($this->usesUnversionedAssetPackage($file)) {
                return $this->assetsPackages->getUrl($file, 'unversioned');
            }

            return $this->assetsPackages->getUrl($file);
        });

        return $image;
    }

    private function usesUnversionedAssetPackage(string $file): bool
    {
        $normalized = ltrim($file, '/');

        return str_starts_with($normalized, 'cache/') || str_starts_with($normalized, 'files/');
    }
}
