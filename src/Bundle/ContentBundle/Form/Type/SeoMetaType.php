<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\FormTypeBundle\Form\Type\SortableCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SeoMetaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('focusKeyphrase', TextType::class, [
            'priority' => 999,
        ]);

        $builder->add('metaTitle', TextType::class, [
            'priority' => 990,
        ]);

        $builder->add('metaSlug', TextType::class, [
            'priority' => 980,
        ]);

        $builder->add('metaDescription', TextareaType::class, [
            'priority' => 970,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_seo_meta';
    }
}
