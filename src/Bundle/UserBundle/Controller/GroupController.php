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
use Integrated\Bundle\IntegratedBundle\Controller\PaginationQueryTrait;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\GroupFormType;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends AbstractController
{
    use PaginationQueryTrait;

    private GroupManagerInterface $manager;
    private PaginatorInterface $paginator;

    public function __construct(GroupManagerInterface $manager, PaginatorInterface $paginator)
    {
        $this->manager = $manager;
        $this->paginator = $paginator;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $paginator = $this->paginator->paginate(
            $this->manager->findAll(),
            $this->getPositiveIntQueryParameter($request, 'page', 1),
            15
        );

        return $this->render('@IntegratedUser/group/index.html.twig', [
            'groups' => $paginator,
        ]);
    }

    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createNewForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_group_index');
            }

            if ($form->isValid()) {
                $user = $form->getData();
                if (!$this->canManageAdminPrivileges() && $this->groupHasAdminRole($user)) {
                    $errorTarget = $form->has('roles') ? $form->get('roles') : $form;
                    $errorTarget->addError(new FormError('You are not allowed to assign the administrator role.'));

                    return $this->render('@IntegratedUser/group/new.html.twig', [
                        'form' => $form,
                    ]);
                }

                $this->manager->persist($user);
                $this->addFlash('success', \sprintf('The group %s is created', $user->getName()));

                return $this->redirectToRoute('integrated_user_group_index');
            }
        }

        return $this->render('@IntegratedUser/group/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $group = $this->manager->find($request->get('id'));

        if (!$group) {
            throw $this->createNotFoundException();
        }
        if (!$this->canManageAdminPrivileges() && $this->groupHasAdminRole($group)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createEditForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_group_index');
            }

            if ($form->isValid()) {
                if (!$this->canManageAdminPrivileges() && $this->groupHasAdminRole($group)) {
                    $errorTarget = $form->has('roles') ? $form->get('roles') : $form;
                    $errorTarget->addError(new FormError('You are not allowed to assign the administrator role.'));

                    return $this->render('@IntegratedUser/group/edit.html.twig', [
                        'group' => $group,
                        'form' => $form,
                    ]);
                }

                $this->manager->persist($group);
                $this->addFlash('success', \sprintf('The changes to the group %s are saved', $group->getName()));

                return $this->redirectToRoute('integrated_user_group_index');
            }
        }

        return $this->render('@IntegratedUser/group/edit.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    public function delete(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $group = $this->manager->find($request->get('id'));

        if (!$group) {
            return $this->redirectToRoute('integrated_user_group_index'); // group is already gone
        }
        if (!$this->canManageAdminPrivileges() && $this->groupHasAdminRole($group)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createDeleteForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // check for cancel click else its a submit
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_group_index');
            }

            if ($form->isValid()) {
                $this->manager->remove($group);
                $this->addFlash('success', \sprintf('The group %s is removed', $group->getName()));

                return $this->redirectToRoute('integrated_user_group_index');
            }
        }

        return $this->render('@IntegratedUser/group/delete.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    private function createNewForm(): Form
    {
        $form = $this->createForm(GroupFormType::class, null, [
            'action' => $this->generateUrl('integrated_user_group_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    private function createEditForm(GroupInterface $group): Form
    {
        $form = $this->createForm(GroupFormType::class, $group, [
            'action' => $this->generateUrl('integrated_user_group_edit', ['id' => $group->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    private function createDeleteForm(GroupInterface $group): Form
    {
        $form = $this->createForm(DeleteFormType::class, $group, [
            'action' => $this->generateUrl('integrated_user_group_delete', ['id' => $group->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }

    private function canManageAdminPrivileges(): bool
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    private function groupHasAdminRole(GroupInterface $group): bool
    {
        $roles = $group->getRoles();

        return \is_array($roles) && \in_array('ROLE_ADMIN', $roles, true);
    }
}
