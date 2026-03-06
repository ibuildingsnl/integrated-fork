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

use Integrated\Bundle\ContentBundle\Bulk\PremiumHandler;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PremiumAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BulkActionPremiumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'premium',
            CheckboxSwitcherType::class,
            [
                'label' => false,
                'required' => false,
                'attr' => [
                    'style' => 'switcher',
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['premium_handler', 'label'])
            ->setAllowedTypes('premium_handler', 'string')
            ->setAllowedTypes('label', 'string')
            ->setDefault('data_class', PremiumAction::class)
            ->setDefault('empty_data', function (Options $options) {
                return new PremiumAction($options['premium_handler']);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_premium';
    }
}
