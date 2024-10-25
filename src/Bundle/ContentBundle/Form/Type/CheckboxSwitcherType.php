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

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class CheckboxSwitcherType extends CheckboxType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $emptyData = function (FormInterface $form, $viewData) {
            return $viewData;
        };

        $resolver->setDefaults(
            [
                'value' => '1',
                'empty_data' => $emptyData,
                'compound' => false,
                'false_values' => [null],
                'invalid_message' => function (Options $options, $previousValue) {
                    return ($options['legacy_error_messages'] ?? true)
                        ? $previousValue
                        : 'The checkbox has an invalid value.';
                },
                'is_empty_callback' => static function ($modelData): bool {
                    return false === $modelData;
                },
                'attr' => [
                    'align_with_widget' => true,
                    'style' => 'switcher',
                ],
            ]
        );

        $resolver->setAllowedTypes('false_values', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'checkbox_switcher';
    }
}
