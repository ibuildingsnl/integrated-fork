<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\FormTypeBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ContentSelectionsType extends AbstractType
{
    private const TITLE = 'blocks';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(self::TITLE, CollectionType::class, [
            'entry_type' => ContentSelectionType::class,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }
}
