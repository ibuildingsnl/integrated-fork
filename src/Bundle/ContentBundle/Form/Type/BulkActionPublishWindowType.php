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

use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PublishWindowAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BulkActionPublishWindowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'startDate',
            DateTimeType::class,
            [
                'label' => false,
                'required' => false,
                'placeholder' => ' ',
                'attr' => [
                    'data-set-date-text' => 'Set publication date',
                ],
                'html5' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ]
        );

        $builder->add(
            'endDate',
            DateTimeType::class,
            [
                'label' => false,
                'required' => false,
                'placeholder' => ' ',
                'attr' => [
                    'data-set-date-text' => 'Set depublication date',
                ],
                'html5' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['publish_window_handler', 'label'])
            ->setAllowedTypes('publish_window_handler', 'string')
            ->setAllowedTypes('label', 'string')
            ->setDefault('data_class', PublishWindowAction::class)
            ->setDefault('empty_data', function (Options $options) {
                return new PublishWindowAction($options['publish_window_handler']);
            })
            ->setDefault('constraints', [
                new Callback(function (?PublishWindowAction $action, ExecutionContextInterface $context): void {
                    if (!$action) {
                        return;
                    }

                    $startDate = $action->getStartDate();
                    $endDate = $action->getEndDate();

                    if ($startDate instanceof \DateTimeInterface && $endDate instanceof \DateTimeInterface && $endDate < $startDate) {
                        $context->buildViolation("The end date can't be earlier than the begin date")
                            ->atPath('endDate')
                            ->addViolation();
                    }
                }),
            ])
            ;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_publish_window';
    }
}
