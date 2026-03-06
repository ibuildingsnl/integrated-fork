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

use Integrated\Bundle\ContentBundle\Document\Bulk\Action\CanonicalAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<CanonicalAction> */
class BulkActionCanonicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'source',
            TextType::class,
            [
                'label' => 'Source',
                'required' => false,
            ]
        );

        $builder->add(
            'sourceUrl',
            UrlType::class,
            [
                'label' => 'Source URL',
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['canonical_handler', 'label'])
            ->setAllowedTypes('canonical_handler', 'string')
            ->setAllowedTypes('label', 'string')
            ->setDefault('data_class', CanonicalAction::class)
            ->setDefault('empty_data', function (Options $options) {
                return new CanonicalAction($options['canonical_handler']);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_canonical';
    }
}
