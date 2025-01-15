<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];

        $attr = [];
        if ($data instanceof Publication) {
            $data = $data->getSettings() + ['time' => $data->getTime()];
            $attr = ['data-publication-status' => $options['data']->getStatus()];
        }

        $builder->add('settings', $options['settings'], [
            'label' => $options['label'],
            'data' => $data,
            'attr' => $attr,
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('settings');
        $resolver->setAllowedTypes('settings', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_publication_settings';
    }
}
