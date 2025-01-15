<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\MultipleFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MediaGalleryMultipleType extends AbstractType
{
    /**
     * @var \Doctrine\ODM\MongoDB\Repository\DocumentRepository
     */
    private $repository;

    public function __construct(DocumentManager $manager)
    {
        $this->repository = $manager->getRepository(File::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new MultipleFileTransformer($this->repository));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-multiple'] = \is_bool($view->vars['attr']['data-multiple']) ? $view->vars['attr']['data-multiple'] : true;
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_media_gallery_image';
    }
}
