<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeoMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('focusKeyphrase', TextType::class, [
            'priority' => 999,
        ]);

        $builder->add('metaTitle', TextType::class, [
            'priority' => 990,
        ]);

        $builder->add('metaDescription', TextareaType::class, [
            'priority' => 970,
        ]);

        $builder->add('seoScore', TextType::class, [
            'priority' => 980,
        ]);

        $builder->add('readabilityScore', TextType::class, [
            'priority' => 980,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => SeoMeta::class,
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'integrated_seo_meta';
    }
}
