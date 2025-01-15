<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Form\Type;

use Integrated\Bundle\UserBundle\Model\ScopeManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Michael Jongman <michael@e-active.nl>
 */
class ScopeFormType extends AbstractType
{
    /**
     * @var ScopeManagerInterface
     */
    private $manager;

    /**
     * ScopeFormType constructor.
     */
    public function __construct(ScopeManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return $this->getManager()->create();
        });
        $resolver->setDefault('data_class', $this->getManager()->getClassName());
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_user_scope_form';
    }

    /**
     * @return ScopeManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }
}
