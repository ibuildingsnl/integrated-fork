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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RoleType extends AbstractType
{
    /**
     * @var RoleManager
     */
    private $manager;
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * RoleType constructor.
     */
    public function __construct(RoleManager $manager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->manager = $manager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $roles = $this->manager->getRolesFromSources();
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            unset($roles['ROLE_ADMIN']);
        }

        $resolver->setDefault('multiple', true);

        $resolver->setDefaults([
            'choices' => array_flip($roles),
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
