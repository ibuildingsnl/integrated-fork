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

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * A lazily rendered image URL backed by LiipImagine.
 *
 * It mimics the small subset of the (removed) Gregwar image handler API that is
 * actually used in templates, so call sites can keep reading as
 * `image(foo).cropResize(150, 150).guess(60)`. Every transformation returns a
 * new instance; the URL is only generated when the object is cast to a string.
 *
 * Transformations are passed to LiipImagine as a runtime configuration on top of
 * one of the base filter sets registered by IntegratedImageExtension::prepend().
 */
class ImageUrl implements \Stringable
{
    /**
     * Gregwar treated a zero or null dimension as "unconstrained". LiipImagine
     * needs a number, so an effectively unreachable bound is used instead.
     */
    private const UNCONSTRAINED = 100000;

    private ?string $mode = null;

    private ?string $format = null;

    /**
     * Runtime filter configuration, keyed by filter name. LiipImagine nests it
     * under the "filters" key of the filter set itself.
     *
     * @var array<string, mixed>
     */
    private array $runtimeConfig = [];

    /**
     * @param string|null $path     Path relative to one of the LiipImagine data roots,
     *                              or null when the image could not be resolved.
     * @param string|null $original Absolute or root relative URL of the untouched image,
     *                              used for pass-through (mimic) formats and as fallback.
     */
    public function __construct(
        private CacheManager $cacheManager,
        private ?string $path,
        private ?string $original = null,
        private bool $passthrough = false,
    ) {
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }

    /**
     * Scales the image down so it fits inside the given box, preserving the
     * aspect ratio. Despite its name this is what Gregwar's cropResize() did:
     * it never actually cropped.
     */
    public function cropResize(int|float|null $width = null, int|float|null $height = null): self
    {
        return $this->withThumbnail('inset', $width, $height);
    }

    /**
     * Scales the image so it covers the given box and crops the overflow.
     */
    public function zoomCrop(int|float|null $width = null, int|float|null $height = null): self
    {
        return $this->withThumbnail('outbound', $width, $height);
    }

    /**
     * Scales the image down so it fits inside the given box, preserving the
     * aspect ratio.
     */
    public function resize(int|float|null $width = null, int|float|null $height = null): self
    {
        return $this->withThumbnail('inset', $width, $height);
    }

    /**
     * Renders the image.
     *
     * Gregwar used this to guess the output format and to set the encoding
     * quality. LiipImagine derives the format from the source image and takes
     * the quality from the filter set, which cannot be overridden at runtime,
     * so both arguments are ignored.
     */
    public function guess(?int $quality = null): self
    {
        return $this;
    }

    /**
     * Forces the image to be rendered as a JPEG.
     */
    public function jpeg(?int $quality = null): self
    {
        $clone = clone $this;
        $clone->format = 'jpeg';

        return $clone;
    }

    public function getUrl(): string
    {
        if ($this->passthrough || $this->mode === null) {
            return (string) ($this->original ?? $this->path);
        }

        if ($this->path === null) {
            return (string) $this->original;
        }

        try {
            return self::toRelativeUrl(
                $this->cacheManager->getBrowserPath($this->path, $this->getFilterName(), $this->runtimeConfig)
            );
        } catch (\Exception) {
            return (string) $this->original;
        }
    }

    /**
     * LiipImagine hands out absolute URLs, but templates prepend the host
     * themselves where they need one, so only the path is kept.
     */
    private static function toRelativeUrl(string $url): string
    {
        if (!preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $parts = parse_url($url);

        if ($parts === false || !isset($parts['path'])) {
            return $url;
        }

        return $parts['path'].(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    private function withThumbnail(string $mode, int|float|null $width, int|float|null $height): self
    {
        $clone = clone $this;

        if ($clone->passthrough) {
            return $clone;
        }

        $clone->mode = $mode;
        $clone->runtimeConfig['thumbnail']['size'] = [
            $width ? (int) round($width) : self::UNCONSTRAINED,
            $height ? (int) round($height) : self::UNCONSTRAINED,
        ];

        return $clone;
    }

    /**
     * Name of the base filter set the runtime configuration is applied to, see
     * IntegratedImageExtension::prepend().
     */
    private function getFilterName(): string
    {
        return 'integrated_'.$this->mode.($this->format === null ? '' : '_'.$this->format);
    }
}
