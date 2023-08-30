<?php

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BrandChannelChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('publish', CheckboxType::class, [
            'required' => false,
            'value' => $options['publish'],
            'label' => $options['brand_name'],
        ]);
        $builder->add('channels', ChoiceType::class, [
            'label' => false,
            'choices' => $options['links'],
            'choice_label' => 'type.name',
            'choice_value' => 'channel.id',
            'choice_attr' => fn(ChannelLink $link) => is_array($options['choice_attr']) ?
                $options['choice_attr'] :
                $options['choice_attr']($link->channel),
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['brand_name', 'links']);
        $resolver->setDefault('publish', false);
        $resolver->setDefault('choice_attr', []);
    }
}
