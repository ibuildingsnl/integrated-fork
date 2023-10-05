<?php

namespace Integrated\Bundle\LinkedInBundle\Form;

use Integrated\Bundle\ContentBundle\Form\Type\PublishTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LinkedInPublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('time', PublishTimeType::class, ['label' => false]);
        $builder->add('title', TextType::class);
        $builder->add('text', TextareaType::class);
    }
}
