<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ImageBundle\Twig\Extension;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage;
use Integrated\Bundle\ImageBundle\Converter\WebFormatConverter;
use Integrated\Bundle\ImageBundle\Factory\StorageModelFactory;
use Integrated\Bundle\ImageBundle\Image\ImageUrl;
use Integrated\Bundle\ImageBundle\Image\LiipImageHandling;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class ImageExtension extends AbstractExtension
{
    public function __construct(
        private LiipImageHandling $imageHandling,
        private WebFormatConverter $webFormatConverter,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('integrated_image', [$this, 'image'], ['is_safe' => ['html']]),
            new TwigFunction('integrated_image_credits', [$this, 'imageCredits'], ['is_safe' => ['html']]),
            new TwigFunction('integrated_image_description', [$this, 'imageDescription'], ['is_safe' => ['html']]),
            new TwigFunction('image_json', [$this, 'imageJson'], ['is_safe' => ['html']]),
            new TwigFunction('web_image', [$this, 'webImage'], ['is_safe' => ['html']]),
            new TwigFunction('image', [$this, 'image'], ['is_safe' => ['html']]),
        ];
    }

    public function imageJson($image): ImageUrl
    {
        if ($json = json_decode($image)) {
            $storageModel = StorageModelFactory::json($json);

            // Returns the image in a webformat
            try {
                $image = $this->webFormatConverter->convert($storageModel)->getPathname();
            } catch (\Exception $e) {
                // Set the fallback image
                $image = $storageModel->getIdentifier();
            }
        }

        return $this->imageHandling->open($image);
    }

    public function webImage($image): ImageUrl
    {
        if ($image instanceof StorageInterface) {
            try {
                // Returns the image in a webformat
                return $this->imageHandling->open($this->webFormatConverter->convert($image)->getPathname());
            } catch (\Exception $e) {
                $image = $image->getIdentifier();
            }
        }

        return $this->imageHandling->webImage((string) $image);
    }

    public function image($image): ImageUrl
    {
        if ($image instanceof StorageInterface) {
            try {
                $image = $this->webFormatConverter->convert($image)->getPathname();
            } catch (\Exception $e) {
                $image = $image->getIdentifier();
            }
        } elseif (\is_string($image) && strpos($image, '{') === 0) {
            // detect json format
            return $this->imageJson($image);
        }

        return $this->imageHandling->open($image);
    }

    /**
     * @return string
     */
    public function imageCredits($image)
    {
        if ($image instanceof Storage) {
            return $image->getMetadata()->getCredits();
        }

        // detect json format
        if (strpos($image, '{') === 0) {
            $imageData = @json_decode($image);

            return $imageData->metadata->credits ?? null;
        }

        return null;
    }

    /**
     * @return string
     */
    public function imageDescription($image)
    {
        if ($image instanceof Storage) {
            return $image->getMetadata()->getDescription();
        }

        // detect json format
        if (strpos($image, '{') === 0) {
            $imageData = @json_decode($image);

            return $imageData->metadata->description ?? null;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated_image_json';
    }
}
