<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestEmailAddressesType extends AbstractType
{
    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => EmailType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'prototype' => true,
            'add_button_text' => 'Add Address',
            'entry_options' => [
                'label' => 'Email address',
            ],
        ]);
    }
}
