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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $brandName = strtolower($options['brand_name']); // Convert to lower case
        $brandName = preg_replace('/\s+/', '', $brandName); // Strip spaces
        $brandName = preg_replace('/[^a-z0-9]/', '', $brandName); // Remove special characters

        $builder->add('publish', CheckboxType::class, [
            'required' => false,
            'value' => $brandName,
            'label' => $options['brand_name'],
            'attr' => [
                'class' => 'brand-choice',
            ],
        ]);

        $builder->add('channels', ChoiceType::class, [
            'label' => false,
            'choices' => $options['links'],
            'choice_label' => 'type.name',
            'choice_value' => 'channel.id',
            'choice_attr' => fn (ChannelLink $link) => array_merge(
                [
                    'class' => 'brand-channel-choice',
                    'data-can-be-primary' => $link->type->canBePrimary ? 'yes' : 'no',
                    'data-channel-type' => $link->getName(),
                    'data-channel-type-icon' => $link->type->getIcon(),
                    'data-channel-default' => $link->default ? true : false,
                ],
                \is_array($options['choice_attr']) ? $options['choice_attr'] : $options['choice_attr']($link->channel),
            ),
            'multiple' => true,
            'expanded' => true,
            'attr' => [
                'class' => 'brand-channels',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['brand_name', 'links']);
        $resolver->setDefault('publish', false);
        $resolver->setDefault('choice_attr', []);
    }
}
