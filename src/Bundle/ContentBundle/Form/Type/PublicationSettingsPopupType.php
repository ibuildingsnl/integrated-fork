<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationSettingsPopupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('settings', $options['settings']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('settings');
        $resolver->setAllowedTypes('settings', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_publication_settings';
    }
}
