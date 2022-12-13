<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ChannelBundle\Form\Type;

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
                    'options' => [
                        'label' => 'Create',
                        'button_class' => 'orange no-icon',
                        'translation_domain' => 'IntegratedChannelBundle',
                    ],
                ],
                'save' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Update',
                        'button_class' => 'green no-icon',
                        'translation_domain' => 'IntegratedChannelBundle',
                    ],
                ],
                'delete' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Delete',
                        'button_class' => 'orange',
                        'translation_domain' => 'IntegratedChannelBundle',
                    ],
                ],
                'cancel' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Back',
                        'button_class' => 'white icon-left',
                        'translation_domain' => 'IntegratedChannelBundle',
                        'attr' => [
                            'formnovalidate' => 'formnovalidate',
                            'data-dismiss' => 'modal',
                            'icon' => 'arrow-left',
                        ],
                    ],
                ],
                'copy' => [
                    'type' => SubmitType::class,
                    'options' => [
                        'label' => 'Copy pages',
                        'button_class' => 'green no-icon',
                        'attr' => [
                            'onclick' => '$(\'#page_copy_action\').val(\'\');',
                        ],
                    ]
                ]
            ]
        );
    }
}
