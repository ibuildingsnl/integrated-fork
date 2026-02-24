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

use Integrated\Bundle\ContentBundle\Document\Bulk\Action\WorkflowStateAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BulkActionWorkflowStateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'state',
            ChoiceType::class,
            [
                'required' => true,
                'label' => false,
                'choices' => $options['state_choices'],
                'placeholder' => 'Select status',
                'attr' => [
                    'class' => 'basic-multiple form-control',
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['workflow_state_handler', 'state_choices', 'label'])
            ->setAllowedTypes('workflow_state_handler', 'string')
            ->setAllowedTypes('state_choices', 'array')
            ->setAllowedTypes('label', 'string')
            ->setDefault('data_class', WorkflowStateAction::class)
            ->setDefault('empty_data', function (Options $options) {
                return new WorkflowStateAction($options['workflow_state_handler']);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_workflow_state';
    }
}
