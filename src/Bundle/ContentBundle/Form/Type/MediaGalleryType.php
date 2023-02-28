<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\FileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MediaGalleryType extends AbstractType
{
    /**
     * @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository
     */
    private $repository;

    public function __construct(DocumentManager $manager)
    {
        $this->repository = $manager->getRepository(File::class);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new FileTransformer($this->repository));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (\array_key_exists('data-multiple', $view->vars['attr'])) {
            $dataMultiple = $view->vars['attr']['data-multiple'];
            if ($dataMultiple === false || $dataMultiple === '' || $dataMultiple === 'false') {
                $view->vars['attr']['data-multiple'] = '';
            } else {
                $view->vars['attr']['data-multiple'] = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_media_gallery_image';
    }
}
