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

use Integrated\Bundle\UserBundle\Form\Type\GroupType;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class SearchSelectionType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly GroupManagerInterface $groups,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class);

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('public', ChoiceType::class, [
                'label' => 'Available for',
                'expanded' => true,
                'choices' => [
                    'Everyone' => 1,
                    'Restricted' => 0,
                ],
            ]);
            $builder->add('groupId', GroupType::class, [
                'required' => false,
            ]);
            $builder->get('groupId')->addModelTransformer(new CallbackTransformer(
                fn (?string $id) => $id ? $this->groups->find($id) : null,
                fn (?Group $group) => $group?->getId(),
            ));
        }

        $builder->add('inMenu', CheckboxSwitcherType::class, [
            'required' => false,
            'label' => 'Add to menu',
            'attr' => [
                'align_with_widget' => true,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_search_selection';
    }
}
