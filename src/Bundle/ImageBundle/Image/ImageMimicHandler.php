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

use Gregwar\Image\Source\File;

class ImageMimicHandler extends ImageHandler
{
    public function save($file, $type = 'mimic', $quality = 100)
    {
        try {
            if ($this->source instanceof File) {
                copy($this->source->getFile(), $file);
            } elseif ($this->useFallbackImage) {
                return null === $file ? file_get_contents($this->fallback) : $this->getCacheFallback();
            }
        } catch (\Exception $e) {
            if ($this->useFallbackImage) {
                return null === $file ? file_get_contents($this->fallback) : $this->getCacheFallback();
            }
            throw $e;
        }

        return $file;
    }

    public function correct()
    {
        return true;
    }

    public function guessType()
    {
        if ($this->source instanceof File) {
            return pathinfo($this->source->getFile(), \PATHINFO_EXTENSION);
        }

        return parent::guessType();
    }

    public function __toString()
    {
        return $this->cacheFile($this->guessType());
    }
}
