<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GlobalPublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        if ($data instanceof Publication) {
            $data = $data->getSettings() + ['time' => $data->getTime()];
        }
        $builder->add('settings', $options['settings'], [
            'label' => $options['label'],
            'data' => $data,
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('settings');
        $resolver->setAllowedTypes('settings', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_global_publication_settings';
    }
}
