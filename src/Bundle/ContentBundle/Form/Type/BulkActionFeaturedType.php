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

use Integrated\Bundle\ContentBundle\Document\Bulk\Action\FeaturedAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<FeaturedAction> */
class BulkActionFeaturedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'featured',
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
            ->setRequired(['featured_handler', 'label'])
            ->setAllowedTypes('featured_handler', 'string')
            ->setAllowedTypes('label', 'string')
            ->setDefault('data_class', FeaturedAction::class)
            ->setDefault('empty_data', function (Options $options) {
                return new FeaturedAction($options['featured_handler']);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_featured';
    }
}
