<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType;
use Integrated\Bundle\FormTypeBundle\Form\Type\ColorType;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;
use Integrated\Bundle\WorkflowBundle\Form\EventListener\ExtractTransitionsFromDataListener;
use Integrated\Bundle\WorkflowBundle\Utils\StateVisibleConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class StateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', Type\TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 3]),
            ],
            'attr' => [
                'class' => 'state_name_input_field',
            ],
        ]);

        $builder->add(
            'publishable',
            CheckboxSwitcherType::class,
            [
                'label' => 'Publish',
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ]
        );

        $builder->add(
            'default',
            CheckboxSwitcherType::class,
            [
                'label' => 'Default',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'state_default_input_field',
                    'align_with_widget' => true,
                ],
            ]
        );

        $builder->add('color', ColorType::class, ['label' => 'Status color', 'required' => false]);

        $builder->add(
            'icon',
            TextType::class,
            [
                'label' => 'Status icon',
                'required' => false,
                'attr' => [
                        'help_text' => '<span>You can use any <a href="https://iconoir.com/" target="_blank">Iconoir</a> icon</span>',
                    ],
            ]
        );

        $choiceFlags = [
            'Optional' => StateVisibleConfig::OPTIONAL,
            'Required' => StateVisibleConfig::REQUIRED,
            'Disabled' => StateVisibleConfig::DISABLED,
        ];

        $builder->add('comment', Type\ChoiceType::class, [
            'expanded' => true,
            'choices' => $choiceFlags,
        ]);

        $builder->add('assignee', Type\ChoiceType::class, [
            'expanded' => true,
            'choices' => $choiceFlags,
        ]);

        $builder->add('deadline', Type\ChoiceType::class, [
            'expanded' => true,
            'choices' => $choiceFlags,
        ]);

        $builder->add('permissions', PermissionsType::class, [
            'required' => false,
            'read-placeholder' => 'Inherit from content type',
            'write-placeholder' => 'Inherit from content type',
        ]);

        $builder->add('order', Type\HiddenType::class, [
            'attr' => ['data-itemorder' => 'collection'],
        ]);

        if ($options['transitions'] == 'data') {
            $builder->addEventSubscriber(new ExtractTransitionsFromDataListener());
        }

        if ($options['transitions'] == 'empty') {
            $builder->add('transitions', Type\ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Transitions to',
                'choices' => [],

                'multiple' => true,
                'expanded' => false,

                'attr' => [
                    'class' => 'state_transitions_input_field',
                ],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $emptyData = function (FormInterface $form) {
            return new State();
        };

        $resolver->setDefault('empty_data', $emptyData);
        $resolver->setDefault('data_class', 'Integrated\\Bundle\\WorkflowBundle\\Entity\\Definition\\State');
        $resolver->setDefault('transitions', 'data');

        $resolver->setAllowedValues('transitions', ['data', 'empty', 'none']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_workflow_definition_state';
    }
}
