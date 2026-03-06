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

use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class GroupType extends AbstractType
{
    /**
     * @var GroupManagerInterface
     */
    private $manager;
    private ?AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        GroupManagerInterface $manager,
        ?AuthorizationCheckerInterface $authorizationChecker = null,
    ) {
        $this->manager = $manager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('class', $this->manager->getClassName());
        $resolver->setDefault('choice_label', 'name');
        $resolver->setDefault('choice_filter', function (Options $options) {
            if ($this->authorizationChecker && $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                return null;
            }

            return static function ($group): bool {
                if (!\is_object($group) || !method_exists($group, 'getRoles')) {
                    return true;
                }

                $roles = $group->getRoles();

                return !\is_array($roles) || !\in_array('ROLE_ADMIN', $roles, true);
            };
        });
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_user_group_choice';
    }

    /**
     * @return GroupManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }
}
