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
use Integrated\Bundle\ImageBundle\Services\ImageHandling;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class ImageExtension extends AbstractExtension
{
    /**
     * @var ImageHandling
     */
    private $imageHandling;

    /**
     * @var WebFormatConverter
     */
    private $webFormatConverter;

    /**
     * @var GregwarImageExtension
     */
    private $imageTwig;

    /**
     * @var array
     */
    private $mimicFormats;

    /**
     * @var ImageHandling
     */
    private $imageMimicHandling;

    public function __construct(ImageHandling $imageHandling, GregwarImageExtension $imageTwig, WebFormatConverter $webFormatConverter, array $mimicFormats, ImageHandling $imageMimicHandling)
    {
        $this->imageHandling = $imageHandling;
        $this->webFormatConverter = $webFormatConverter;
        $this->imageTwig = $imageTwig;
        $this->mimicFormats = $mimicFormats;
        $this->imageMimicHandling = $imageMimicHandling;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('integrated_image', $this->image(...), ['is_safe' => ['html']]),
            new TwigFunction('integrated_image_credits', $this->imageCredits(...), ['is_safe' => ['html']]),
            new TwigFunction('integrated_image_description', $this->imageDescription(...), ['is_safe' => ['html']]),
            new TwigFunction('image_json', $this->imageJson(...), ['is_safe' => ['html']]),
            new TwigFunction('web_image', $this->webImage(...), ['is_safe' => ['html']]),
            new TwigFunction('image', $this->image(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return \Integrated\Bundle\ImageBundle\Image\ImageHandler
     */
    public function imageJson($image)
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

            if (\in_array($storageModel->getMetadata()->getExtension(), $this->mimicFormats)) {
                return $this->imageMimicHandling->open($image);
            }
        }

        return $this->imageHandling->open($image);
    }

    /**
     * @return \Integrated\Bundle\ImageBundle\Image\ImageHandler
     */
    public function webImage($image)
    {
        if ($image instanceof StorageInterface) {
            try {
                // Returns the image in a webformat
                return $this->imageHandling->open($this->webFormatConverter->convert($image)->getPathname());
            } catch (\Exception $e) {
                $image = $image->getIdentifier();
            }
        }

        return $this->imageTwig->webImage($image);
    }

    /**
     * @return \Integrated\Bundle\ImageBundle\Image\ImageHandler
     */
    public function image($image)
    {
        if ($image instanceof StorageInterface) {
            $metadata = $image->getMetadata();

            try {
                $image = $this->webFormatConverter->convert($image)->getPathname();
            } catch (\Exception $e) {
                $image = $image->getIdentifier();
            }

            if (\in_array($metadata->getExtension(), $this->mimicFormats)) {
                return $this->imageMimicHandling->open($image);
            }
        } elseif (filter_var($image, \FILTER_VALIDATE_URL)) {
            return $this->imageMimicHandling->open($image);
        }

        // detect json format
        if (str_starts_with($image, '{')) {
            return $this->imageJson($image);
        }

        if (\in_array(pathinfo($image, \PATHINFO_EXTENSION), $this->mimicFormats)) {
            return $this->imageMimicHandling->open($image);
        }

        $extension = pathinfo($image, \PATHINFO_EXTENSION);
        if (strtolower($extension) === 'pdf') {
            return $this->imageHandling->open('bundles/integratedintegrated/images/fallbacks/pdf-fallback.jpg');
        }
        if (file_exists($image)) {
            $mime = mime_content_type($image);
            if (str_starts_with($mime, 'video/')) {
                return $this->imageHandling->open('bundles/integratedintegrated/images/fallbacks/video-fallback.jpg');
            }
        }
        if (!file_exists($image) && (!str_contains($image, '@'))) {
            return $this->imageHandling->open('bundles/integratedintegrated/images/fallbacks/fallback.jpg');
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
        if (str_starts_with($image, '{')) {
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
        if (str_starts_with($image, '{')) {
            $imageData = @json_decode($image);

            return $imageData->metadata->description ?? null;
        }

        return null;
    }

    public function getName()
    {
        return 'integrated_image_json';
    }
}
