<?php

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

use Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ContentOptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('premium', CheckboxSwitcherType::class, [
                'label'    => 'This content is premium',
                'required' => false,
            ])
            ->add('featured', CheckboxSwitcherType::class, [
                'label' => 'This content is featured',
                'required' => false,
            ])->add('dont_use', CheckboxSwitcherType::class, [
                'label' => 'This content is not used for newsletter',
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'content_options';
    }
}
