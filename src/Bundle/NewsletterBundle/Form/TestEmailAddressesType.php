<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class TestEmailAddressesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('recipients', CollectionType::class, [
            'entry_type' => EmailType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'prototype' => true,
            'entry_options' => [
                'label' => 'Test mail recipient email address',
            ],
        ]);
    }
}
