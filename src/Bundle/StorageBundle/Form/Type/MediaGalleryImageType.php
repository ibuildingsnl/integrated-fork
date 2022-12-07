<?php

namespace Integrated\Bundle\StorageBundle\Form\Type;

use Symfony\Contracts\Translation\TranslatorInterface;
use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\ImageBundle\Converter\Container;
use Integrated\Bundle\ImageBundle\Converter\Format\WebFormat;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MediaGalleryImageType extends AbstractDropzoneType
{
    /**
     * @var WebFormat
     */
    protected $webFormat;

    /**
     * @var Container
     */
    protected $converterContainer;

    /**
     * @param AssetManager        $stylesheets
     * @param AssetManager        $javascripts
     * @param TranslatorInterface $translator
     * @param ImageExtension      $imageExtension
     * @param WebFormat           $webFormat
     * @param Container           $converterContainer
     */
    public function __construct(
        AssetManager $stylesheets,
        AssetManager $javascripts,
        TranslatorInterface $translator,
        ImageExtension $imageExtension,
        WebFormat $webFormat,
        Container $converterContainer
    ) {
        $this->webFormat = $webFormat;
        $this->converterContainer = $converterContainer;

        parent::__construct($stylesheets, $javascripts, $translator, $imageExtension, 'image');
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['options']['extensions'] = array_merge(
            $this->webFormat->getWebFormats()->toArray(),
            $this->converterContainer->formats()->toArray()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_media_gallery_image';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ImageType::class;
    }
}
