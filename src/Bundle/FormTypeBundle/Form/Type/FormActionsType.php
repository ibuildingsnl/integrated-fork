<?php

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormActionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['buttons'] as $name => $config) {
            $this->addButton($builder, $name, $config);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->count() == 0) {
            return;
        }

        array_map([$this, 'validateButton'], $form->all());
    }

    /**
     * Adds a button.
     *
     * @param FormBuilderInterface $builder
     * @param string               $name
     * @param array                $config
     *
     * @throws \InvalidArgumentException
     */
    protected function addButton($builder, $name, $config)
    {
        $options = (isset($config['options'])) ? $config['options'] : [];
        $builder->add($name, $config['type'], $options);
    }

    /**
     * Validates if child is a Button.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateButton(FormInterface $field)
    {
        if (!$field instanceof Button) {
            throw new \InvalidArgumentException('Children of FormActionsType must be instances of the Button class');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'buttons' => [],
            'options' => [],
            'mapped' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'form_actions';
    }
}
