<?php

namespace Integrated\Bundle\ImageBundle\Twig\Extension;

use Integrated\Bundle\ImageBundle\Services\ImageHandling;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * ImageTwig extension.
 *
 * @author Gregwar <g.passault@gmail.com>
 * @author bzikarsky <benjamin.zikarsky@perbility.de>
 */
class GregwarImageExtension extends AbstractExtension
{
    /**
     * @var ImageHandling
     */
    private $imageHandling;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @param string $webDir
     */
    public function __construct(ImageHandling $imageHandling, $webDir)
    {
        $this->imageHandling = $imageHandling;
        $this->webDir = $webDir;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('image', $this->image(...), ['is_safe' => ['html']]),
            new TwigFunction('new_image', $this->newImage(...), ['is_safe' => ['html']]),
            new TwigFunction('web_image', $this->webImage(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $path
     *
     * @return object
     */
    public function webImage($path)
    {
        $directory = $this->webDir.'/';

        return $this->imageHandling->open($directory.$path);
    }

    /**
     * @param string $path
     *
     * @return object
     */
    public function image($path)
    {
        return $this->imageHandling->open($path);
    }

    /**
     * @param string $width
     * @param string $height
     *
     * @return object
     */
    public function newImage($width, $height)
    {
        return $this->imageHandling->create($width, $height);
    }

    public function getName()
    {
        return 'image';
    }
}
