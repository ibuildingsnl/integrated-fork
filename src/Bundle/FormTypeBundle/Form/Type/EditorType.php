<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Johnny Borg <johnnny@e-active.nl>
 */
class EditorType extends AbstractType
{
    public const RELATION = '__editor_image';

    /**
     * @var array
     */
    private $contentStyles;

    public function __construct(array $contentStyles)
    {
        $this->contentStyles = $contentStyles;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['mode'] = $options['mode'];
        $view->vars['content_styles'] = $this->contentStyles;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mode' => 'default',
            'empty_data' => '',
        ]);

        $resolver->setAllowedTypes('mode', 'string');
    }

    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_editor';
    }
}
