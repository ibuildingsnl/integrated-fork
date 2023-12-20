<?php

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RefreshableChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('choice', ChoiceType::class, $options['select']);
        $builder->add('refresh', SubmitType::class, $options['submit']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'select' => [],
            'submit' => [
                'label' => '',
                'icon' => 'refresh-double'
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'integrated_refreshable_choice';
    }
}
