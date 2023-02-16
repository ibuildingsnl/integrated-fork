<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SocialsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('social', TailwindCollectionType::class, [
            'entry_type' => SocialType::class,
            'priority' => 480,
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add social',
            'label' => 'Socials',
            'attr' => ['location' => 'editor', 'style' => 'editor', 'state' => 'show'],
        ]);
    }
}
