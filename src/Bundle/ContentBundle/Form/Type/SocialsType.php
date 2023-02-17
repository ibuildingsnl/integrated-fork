<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SocialsType extends AbstractType
{
    public function getParent(): string
    {
        return TailwindCollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => SocialType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add social',
            'label' => 'Socials',
            'attr' => ['location' => 'editor', 'style' => 'editor', 'state' => 'show'],
        ]);
    }
}
