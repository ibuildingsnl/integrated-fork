<?php

namespace Integrated\Bundle\InstagramBundle\Form;

use Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryMultipleType;
use Integrated\Bundle\ContentBundle\Form\Type\PublishTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InstagramPublicationType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('time', PublishTimeType::class, ['label' => false]);
        $builder->add('images', MediaGalleryMultipleType::class, [
            'label' => false,
            'attr' => [
                'data-types' => '[{"type":"image","name":"Image"}]',
                'data-emptytext' => 'Select images',
                'data-multiple' => true
            ]
        ]);
        $builder->add('caption', TextareaType::class);
    }

}
