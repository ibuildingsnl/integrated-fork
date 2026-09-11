<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ImageBundle\Image;

use Integrated\Bundle\ImageBundle\Converter\WebFormatConverter;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * Creates lazily rendered, LiipImagine backed image URLs.
 *
 * This is the replacement for Gregwar's ImageHandling service: it takes the
 * same kind of input (an absolute file path, a public path, a bundle resource
 * path or a remote URL) and returns an object that can be transformed and cast
 * to a string in templates.
 */
class LiipImageHandling
{
    /**
     * @param string[] $dataRoots        Absolute paths configured as LiipImagine data roots.
     * @param string[] $mimicFormats     Extensions that must be served untouched, e.g. svg.
     * @param int      $maxSourcePixels  Number of pixels a source image may have before it is
     *                                   published untransformed, or 0 to always transform.
     * @param string   $webRoot          Absolute path of the directory that is served over http,
     *                                   used to tell published files from files that still have
     *                                   to be handed out through the image cache.
     */
    public function __construct(
        private CacheManager $cacheManager,
        private DataManager $dataManager,
        private FileLocatorInterface $fileLocator,
        private WebFormatConverter $webFormatConverter,
        private array $dataRoots,
        private array $mimicFormats = [],
        private string $fallbackImage = '',
        private string $publicPrefix = '',
        private int $maxSourcePixels = 0,
        private string $webRoot = '',
    ) {
    }

    /**
     * @param string|\SplFileInfo|StorageInterface|\Stringable|null $file
     */
    public function open($file): ImageUrl
    {
        if ($file instanceof StorageInterface) {
            // The storage pathname may point to a remote filesystem, so make
            // sure a local copy exists that LiipImagine is able to load.
            try {
                $file = $this->webFormatConverter->convert($file);
            } catch (\Exception) {
                return $this->passthrough((string) $file);
            }
        }

        if ($file instanceof \SplFileInfo) {
            $file = $file->getPathname();
        } elseif (\is_object($file) && method_exists($file, '__toString')) {
            $file = (string) $file;
        }

        if (!\is_string($file) || $file === '') {
            return $this->passthrough(null);
        }

        if (str_starts_with($file, '@')) {
            try {
                $file = $this->fileLocator->locate($file);
            } catch (\InvalidArgumentException) {
                return $this->passthrough(null);
            }
        }

        // Remote images cannot be resolved by the filesystem data loader.
        if (filter_var($file, \FILTER_VALIDATE_URL)) {
            return $this->passthrough($file);
        }

        if ($canonical = realpath($file)) {
            $file = $canonical;
        }

        if ($this->isMimicFormat($file)) {
            return $this->mimic($file);
        }

        $relative = $this->makeRelative($file);

        if ($relative === null) {
            return $this->passthrough($this->toPublicUrl($file));
        }

        return new ImageUrl(
            $this->cacheManager,
            $this->dataManager,
            $relative,
            $this->toPublicUrl($file),
            false,
            $file,
            $this->maxSourcePixels
        );
    }

    /**
     * Opens a path that is relative to the public web directory.
     */
    public function webImage(string $path): ImageUrl
    {
        return $this->open('/'.ltrim($path, '/'));
    }

    /**
     * Strips a known data root from an absolute path so LiipImagine can resolve
     * it. Returns null when the path is outside every configured root.
     */
    public function makeRelative(string $path): ?string
    {
        foreach ($this->dataRoots as $root) {
            $root = rtrim($root, '/');

            if ($root !== '' && str_starts_with($path, $root.'/')) {
                return substr($path, \strlen($root));
            }
        }

        // Not an existing file outside the data roots, so it must already be a
        // path relative to one of them.
        if (!is_file($path)) {
            return '/'.ltrim($path, '/');
        }

        return null;
    }

    private function passthrough(?string $url): ImageUrl
    {
        return new ImageUrl($this->cacheManager, $this->dataManager, null, $url ?: $this->fallbackImage, true);
    }

    /**
     * Hands out a file whose format is never touched.
     *
     * A published file is served straight from its own URL, but a file that only
     * exists on disk, such as the local copy of a remote file in the storage
     * cache, has to be published through the image cache first. It is copied
     * byte for byte, so the format is still left alone.
     */
    private function mimic(string $file): ImageUrl
    {
        if ($this->isPublished($file)) {
            return $this->passthrough($this->toPublicUrl($file));
        }

        $relative = $this->makeRelative($file);

        if ($relative === null) {
            return $this->passthrough($this->toPublicUrl($file));
        }

        return new ImageUrl(
            $this->cacheManager,
            $this->dataManager,
            $relative,
            $this->toPublicUrl($file),
            false,
            $file,
            0,
            true
        );
    }

    /**
     * Whether the file is already reachable over http, either because it is not
     * a file on disk at all, and therefore is a URL, or because it lives inside
     * the directory that is served.
     */
    private function isPublished(string $file): bool
    {
        if (!is_file($file)) {
            return true;
        }

        $root = rtrim($this->webRoot, '/');

        return $root !== '' && str_starts_with($file, $root.'/');
    }

    private function isMimicFormat(string $file): bool
    {
        return \in_array(strtolower(pathinfo($file, \PATHINFO_EXTENSION)), $this->mimicFormats, true);
    }

    /**
     * Best effort public URL of the untouched file, used for pass-through
     * formats and whenever the image cannot be processed.
     */
    private function toPublicUrl(string $file): string
    {
        $relative = null;

        foreach ($this->dataRoots as $root) {
            $root = rtrim($root, '/');

            if ($root !== '' && str_starts_with($file, $root.'/')) {
                $relative = substr($file, \strlen($root));

                break;
            }
        }

        if ($relative === null) {
            $relative = str_starts_with($file, '/') ? $file : '/'.$file;
        }

        return $this->publicPrefix.$relative;
    }
}
