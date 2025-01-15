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

use Integrated\Bundle\FormTypeBundle\Form\Type\FormActionsType;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class TwoFactorController extends AbstractController
{
    private UserManagerInterface $manager;
    private TranslatorInterface $translator;

    public function __construct(UserManagerInterface $manager, TranslatorInterface $translator)
    {
        $this->manager = $manager;
        $this->translator = $translator;
    }

    public function delete(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->manager->find($request->get('id'));

        if (!$user || !$user->isGoogleAuthenticatorEnabled()) {
            return $this->redirectToRoute('integrated_user_user_index');
        }

        /** @var Form $form */
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                $user->setGoogleAuthenticatorSecret(null);

                $this->manager->persist($user);

                $translation = $this->translator->trans('The two factor authenticator for user %name% is removed', ['%name%' => $user->getUserIdentifier()]);
                $this->addFlash('success', $translation);

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        return $this->render('@IntegratedUser/two_factor/delete.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    private function createDeleteForm(UserInterface $user): Form
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(DeleteFormType::class, $user, [
            'action' => $this->generateUrl('integrated_user_user_delete_authenticator', ['id' => $user->getId()]),
        ]);

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'delete' => ['type' => SubmitType::class, 'options' => ['label' => 'Delete']],
                'cancel' => ['type' => SubmitType::class, 'options' => ['label' => 'Cancel', 'attr' => ['type' => 'default', 'formnovalidate' => true]]],
            ],
        ]);

        return $form;
    }
}
