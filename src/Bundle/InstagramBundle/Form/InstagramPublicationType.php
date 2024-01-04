<?php

namespace Integrated\Bundle\InstagramBundle\Form;

use Integrated\Bundle\ContentBundle\Form\Type\ImageChoiceType;
use Integrated\Bundle\ContentBundle\Form\Type\PublishTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class InstagramPublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('time', PublishTimeType::class, ['label' => false]);
        $builder->add('caption', TextareaType::class);
    }

}
