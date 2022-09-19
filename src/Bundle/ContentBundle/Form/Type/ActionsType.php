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

use Integrated\Common\Form\Type\ActionsType as BaseActionsType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ActionsType extends BaseActionsType
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            [
                'create' => [
                    'type' => SubmitType::class,
                    'options' => ['label' => 'Create', 'button_class' => 'orange no-icon']
                ],
                'save' => [
                    'type' => SubmitType::class,
                    'options' => ['label' => 'Update', 'button_class' => 'green no-icon']
                ],
                'delete' => [
                    'type' => SubmitType::class,
                    'options' => ['label' => 'Delete', 'button_class' => 'orange']
                ],
                'back' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Back',
                        'button_class' => 'white icon-left',
                        'attr' => ['formnovalidate' => 'formnovalidate', 'icon' => 'arrow-left']
                    ]
                ],
                'reload' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Reload',
                        'button_class' => 'orange icon-left',
                        'attr' => ['formnovalidate' => 'formnovalidate', 'icon' => 'rotate']
                    ]
                ],
                'reload_changed' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Reload (keep changes)',
                        'attr' => ['formnovalidate' => 'formnovalidate', 'icon' => 'rotate']
                    ]
                ],
                'cancel' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Back',
                        'button_class' => 'white icon-left',
                        'attr' => [
                            'formnovalidate' => 'formnovalidate',
                            'data-dismiss' => 'modal',
                            'icon' => 'arrow-left'
                        ]
                    ]
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_content_actions';
    }
}
