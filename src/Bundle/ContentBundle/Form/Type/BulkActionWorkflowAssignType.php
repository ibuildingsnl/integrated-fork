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

use Integrated\Bundle\ContentBundle\Document\Bulk\Action\WorkflowAssignAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<WorkflowAssignAction> */
class BulkActionWorkflowAssignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'assigned',
            ChoiceType::class,
            [
                'required' => false,
                'label' => false,
                'choices' => $options['user_choices'],
                'placeholder' => 'Not Assigned',
                'attr' => [
                    'class' => 'basic-multiple form-control',
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['workflow_assign_handler', 'user_choices', 'label'])
            ->setAllowedTypes('workflow_assign_handler', 'string')
            ->setAllowedTypes('user_choices', 'array')
            ->setAllowedTypes('label', 'string')
            ->setDefault('data_class', WorkflowAssignAction::class)
            ->setDefault('empty_data', function (Options $options) {
                return new WorkflowAssignAction($options['workflow_assign_handler']);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_workflow_assign';
    }
}
