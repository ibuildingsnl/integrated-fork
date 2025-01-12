<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Controller;

use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\UserBundle\Form\Type\ProfileFormType;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class ProfileController extends AbstractController
{
    private UserManagerInterface $userManager;
    private PasswordHasherFactoryInterface $hasherFactory;

    public function __construct(UserManagerInterface $userManager, PasswordHasherFactoryInterface $hasherFactory)
    {
        $this->userManager = $userManager;
        $this->hasherFactory = $hasherFactory;
    }

    public function index(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            throw new \LogicException(\sprintf('$user is not and instance of %s', UserInterface::class));
        }

        $form = $this->createProfileForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_content_content_index');
            }

            if ($form->isValid()) {
                $user->setPassword($this->hasherFactory->getPasswordHasher($user)->hash($form->get('password')->getData()));
                $user->setSalt(null);

                $this->userManager->persist($user);
                $this->addFlash('success', 'Your profile have been saved');

                return $this->redirectToRoute('integrated_content_content_index');
            }
        }

        return $this->render('@IntegratedUser/profile/index.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    protected function createProfileForm(UserInterface $user): Form
    {
        $form = $this->createForm(ProfileFormType::class, $user, [
            'action' => $this->generateUrl('integrated_user_profile_index'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }
}
