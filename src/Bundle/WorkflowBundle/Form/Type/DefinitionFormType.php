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

use Integrated\Bundle\FormTypeBundle\Form\Type\SortableCollectionType;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Form\EventListener\ExtractDefaultStateFromCollectionListener;
use Integrated\Common\Validator\Constraints\UniqueEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class DefinitionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 3]),
            ],
        ]);

        $builder->add('states', SortableCollectionType::class, [
            'label' => 'Statuses',
            'entry_type' => StateType::class,
            'entry_options' => ['states' => $builder->getData()?->getStates()],
            'prototype_data' => new Definition\State(),
            'allow_add' => true,
            'allow_delete' => true,
            'default_title' => 'New workflow state',
            'add_button_text' => 'Add workflow state',
            'constraints' => [
                new Count(['min' => 1]),
                new UniqueEntry(['fields' => ['name'], 'caseInsensitive' => true]),
            ],
        ]);

        // Add eventSubscriber which extracts the default State from the State Collection
        $builder->addEventSubscriber(new ExtractDefaultStateFromCollectionListener());
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $child = $view->children['states'];

        $last = array_pop($child->vars['block_prefixes']);

        // add some extra names to block_prefixes to allow for more templating options

        $child->vars['block_prefixes'][] = 'workflow_definition_state_collection';
        $child->vars['block_prefixes'][] = 'integrated_workflow_definition_state_collection';
        $child->vars['block_prefixes'][] = $last;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $emptyData = function (FormInterface $form) {
            return new Definition();
        };

        $resolver->setDefault('empty_data', $emptyData);
        $resolver->setDefault('data_class', 'Integrated\\Bundle\\WorkflowBundle\\Entity\\Definition');
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_workflow_definition';
    }
}
