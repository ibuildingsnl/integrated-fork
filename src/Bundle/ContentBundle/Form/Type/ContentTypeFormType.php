<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Form\Type\ContentType\FieldsType;
use Integrated\Bundle\FormTypeBundle\Form\Type\ColorType;
use Integrated\Common\Form\Mapping\MetadataInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ContentTypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MetadataInterface $metadata */
        $metadata = $options['metadata'];

        $builder
            ->add('name', TextType::class, [
                'priority' => 990,
                'label' => 'Name',
                'attr' => ['location' => 'editor', 'style' => 'inline'],
            ])
            ->add(
                'fields',
                FieldsType::class,
                [
                    'priority' => 980,
                    'metadata' => $metadata,
                    'attr' => [
                        'location' => 'editor',
                        'style' => 'editor',
                        'state' => 'show',
                    ],
                ]
            )
            ->add(
                'channels',
                ContentTypeChannelsType::class,
                [
                    'priority' => 970,
                    'property_path' => 'options[channels]',
                    'attr' => [
                        'location' => 'editor',
                        'style' => 'editor',
                        'state' => 'show',
                    ],
                ]
            );

        $builder->add('options_publication', ChoiceType::class, [
            'label' => 'Publication',
            'choices' => [
                'Publish items on selected channels' => '',
                'Disable for publication' => 'disabled',
            ],
            'property_path' => 'options[publication]',
            'required' => false,
            'attr' => ['location' => 'sidebar', 'style' => 'sidebar', 'state' => 'show', 'icon' => 'link'],
        ]);
        foreach ($metadata->getOptions() as $option) {
            $ype = $builder->create(
                'options_'.$option->getName(),
                $option->getType(),
                [
                    'attr' => [
                        'location' => 'sidebar',
                        'style' => 'sidebar',
                        'state' => 'show',
                        'icon' => 'stackoverflow',
                    ],
                    'label' => ucfirst($option->getName()),
                ] + $option->getOptions()
            )->setPropertyPath('options['.$option->getName().']');

            $builder->add($ype);
        }

        $builder->add(
            $builder->create(
                'permissions',
                FormType::class,
                [
                    'inherit_data' => true,
                    'attr' => [
                        'location' => 'sidebar',
                        'style' => 'sidebar',
                        'state' => 'show',
                        'icon' => 'key-back',
                    ],
                ]
            )->add(
                'permissions',
                PermissionsType::class,
                [
                    'required' => false,
                ]
            ),
        );

        $builder->add('options_color', ColorType::class, [
            'attr' => [
                'location' => 'sidebar',
                'style' => 'sidebar',
                'icon' => 'color-picker',
            ],
            'property_path' => 'options[color]',
            'label' => 'Color',
            'required' => false,
        ]);

        // @todo icon(oir) type?
        $builder->add('options_icon', TextType::class, [
            'attr' => [
                'location' => 'sidebar',
                'style' => 'sidebar',
                'icon' => 'iconoir',
            ],
            'property_path' => 'options[icon]',
            'label' => 'Icon',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['metadata']);
        $resolver->setAllowedTypes('metadata', 'Integrated\\Common\\Form\\Mapping\\MetadataInterface');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_type';
    }
}
