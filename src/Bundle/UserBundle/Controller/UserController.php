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
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\UserFilterType;
use Integrated\Bundle\UserBundle\Form\Type\UserFormType;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Provider\FilterQueryProvider;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    private UserManagerInterface $manager;
    private FilterQueryProvider $provider;
    private PaginatorInterface $paginator;

    public function __construct(UserManagerInterface $manager, FilterQueryProvider $provider, PaginatorInterface $paginator)
    {
        $this->manager = $manager;
        $this->provider = $provider;
        $this->paginator = $paginator;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $data = $request->query->get('integrated_user_filter');

        $users = $this->provider->getUsers($data);

        $facetFilter = $this->createForm(UserFilterType::class, null, [
            'data' => $data,
        ]);
        $facetFilter->handleRequest($request);

        $pagination = $this->paginator->paginate(
            $users,
            $request->query->get('page', 1),
            15
        );

        return $this->render('@IntegratedUser/user/index.html.twig', [
            'users' => $pagination,
            'facetFilter' => $facetFilter,
        ]);
    }

    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        /** @var Form $form */
        $form = $this->createNewForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                $user = $form->getData();

                $this->manager->persist($user);
                $this->addFlash('success', \sprintf('The user %s is created', $user->getUsername()));

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        return $this->render('@IntegratedUser/user/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->manager->find($request->get('id'));

        if (!$user) {
            throw $this->createNotFoundException();
        }

        /** @var Form $form */
        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                $this->manager->persist($user);
                $this->addFlash('success', \sprintf('The changes to the user %s are saved', $user->getUserIdentifier()));

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        return $this->render('@IntegratedUser/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    public function delete(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->manager->find($request->get('id'));

        if (!$user) {
            return $this->redirectToRoute('integrated_user_user_index'); // user is already gone
        }

        /** @var Form $form */
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                $this->manager->remove($user);
                $this->addFlash('success', \sprintf('The user %s is removed', $user->getUserIdentifier()));

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        return $this->render('@IntegratedUser/user/delete.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    protected function createNewForm(): FormInterface
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserFormType::class, null, [
            'action' => $this->generateUrl('integrated_user_user_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    protected function createEditForm(UserInterface $user): FormInterface
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserFormType::class, $user, [
            'action' => $this->generateUrl('integrated_user_user_edit', ['id' => $user->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    protected function createDeleteForm(UserInterface $user): FormInterface
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(DeleteFormType::class, $user, [
            'action' => $this->generateUrl('integrated_user_user_delete', ['id' => $user->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }
}
