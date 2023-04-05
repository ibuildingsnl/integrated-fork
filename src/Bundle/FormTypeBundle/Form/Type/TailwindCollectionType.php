<?php

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TailwindCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace(
            $view->vars,
            [
                'allow_add' => $options['allow_add'],
                'allow_delete' => $options['allow_delete'],
                'add_button_text' => $options['add_button_text'],
                'add_button_class' => $options['add_button_class'],
                'delete_button_text' => $options['delete_button_text'],
                'delete_button_class' => $options['delete_button_class'],
                'prototype_name' => $options['prototype_name'],
            ]
        );

        if ($form->getConfig()->hasAttribute('prototype')) {
            $view->vars['prototype'] = $form->getConfig()->getAttribute('prototype')->createView($view);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $optionsNormalizer = function (Options $options, $value) {
            // @codeCoverageIgnoreStart
            $value['block_name'] = 'entry';

            return $value;
            // @codeCoverageIgnoreEnd
        };

        $defaults = [
            'allow_add' => false,
            'allow_delete' => false,
            'prototype' => true,
            'prototype_name' => '__name__',
            'add_button_text' => 'Add',
            'add_button_class' => 'btn',
            'delete_button_text' => '',
            'delete_button_class' => 'remove',
            'options' => [],
        ];

        $defaults['entry_type'] = TextType::class;

        $resolver->setDefaults($defaults);

        $resolver->setNormalizer('options', $optionsNormalizer);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'tailwind_collection';
    }

    /**
     * Backward compatibility for SF < 3.0.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
