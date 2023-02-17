<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentSelectionsType extends AbstractType
{
    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => ContentSelectionType::class,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }
}
