<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LinkedInConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('token_secret', TextType::class, ['attr' => ['readonly' => 'true']]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_social_twitter';
    }
}
