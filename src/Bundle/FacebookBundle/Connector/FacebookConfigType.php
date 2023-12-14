<?php

namespace Integrated\Bundle\FacebookBundle\Connector;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FacebookConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('token_secret', TextType::class, ['attr' => ['readonly' => 'true']]);
    }

    public function getBlockPrefix()
    {
        return 'integrated_social_facebook';
    }
}
