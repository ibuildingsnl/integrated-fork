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
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\GroupFormType;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends AbstractController
{
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
            $request->query->get('page', 1),
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

        /** @var Form $form */
        $form = $this->createNewForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_group_index');
            }

            if ($form->isValid()) {
                $user = $form->getData();

                $this->manager->persist($user);
                $this->addFlash('success', sprintf('The group %s is created', $user->getName()));

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

        /** @var Form $form */
        $form = $this->createEditForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_group_index');
            }

            if ($form->isValid()) {
                $this->manager->persist($group);
                $this->addFlash('success', sprintf('The changes to the group %s are saved', $group->getName()));

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

        /** @var Form $form */
        $form = $this->createDeleteForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // check for cancel click else its a submit
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_group_index');
            }

            if ($form->isValid()) {
                $this->manager->remove($group);
                $this->addFlash('success', sprintf('The group %s is removed', $group->getName()));

                return $this->redirectToRoute('integrated_user_group_index');
            }
        }

        return $this->render('@IntegratedUser/group/delete.html.twig', [
            'group' => $group,
            'form' => $form,
        ]);
    }

    private function createNewForm(): FormInterface
    {
        $form = $this->createForm(GroupFormType::class, null, [
            'action' => $this->generateUrl('integrated_user_group_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    private function createEditForm(GroupInterface $group): FormInterface
    {
        $form = $this->createForm(GroupFormType::class, $group, [
            'action' => $this->generateUrl('integrated_user_group_edit', ['id' => $group->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    private function createDeleteForm(GroupInterface $group): FormInterface
    {
        $form = $this->createForm(DeleteFormType::class, $group, [
            'action' => $this->generateUrl('integrated_user_group_delete', ['id' => $group->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }
}
