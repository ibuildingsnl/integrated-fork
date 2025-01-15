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

use Integrated\Bundle\FormTypeBundle\Form\Type\Select2Type;
use Integrated\Bundle\UserBundle\Doctrine\RoleManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RoleType extends AbstractType
{
    /**
     * @var RoleManager
     */
    private $manager;

    /**
     * RoleType constructor.
     */
    public function __construct(RoleManager $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('multiple', true);

        $resolver->setDefaults([
            'choices' => array_flip($this->manager->getRolesFromSources()),
            'required' => false,
            'attr' => [
                'class' => 'select2',
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return Select2Type::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_user_role_choice';
    }
}
