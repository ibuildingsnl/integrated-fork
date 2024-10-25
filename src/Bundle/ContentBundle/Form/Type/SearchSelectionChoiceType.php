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

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class SearchSelectionChoiceType extends AbstractType
{
    /**
     * @var \Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelectionRepository
     */
    private $repository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(DocumentManager $manager, TokenStorageInterface $tokenStorage)
    {
        $this->repository = $manager->getRepository(SearchSelection::class);
        $this->tokenStorage = $tokenStorage;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = [];
        if ($user = $this->getUser()) {
            $choices = $this->repository->findForUser($user);
        }

        $resolver->setDefaults([
            'choices' => $choices,
            'choice_label' => 'title',
            'choice_value' => 'id',
            'placeholder' => '',
        ]);
    }

    private function getUser(): ?UserInterface
    {
        if ($token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();

            if ($user instanceof UserInterface) {
                return $user;
            }
        }

        return null;
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_search_selection_choice';
    }
}
