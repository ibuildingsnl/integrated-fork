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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\ScopeFormType;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\ScopeManagerInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeController extends AbstractController
{
    private DocumentManager $documentManager;
    private EntityManager $entityManager;
    private PaginatorInterface $paginator;
    private ScopeManagerInterface $scopeManager;

    public function __construct(DocumentManager $documentManager, EntityManager $entityManager, PaginatorInterface $paginator, ScopeManagerInterface $scopeManager)
    {
        $this->documentManager = $documentManager;
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->scopeManager = $scopeManager;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $paginator = $this->paginator->paginate(
            $this->scopeManager->findAll(),
            $request->query->get('page', 1),
            15
        );

        return $this->render('@IntegratedUser/scope/index.html.twig', [
            'scopes' => $paginator,
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
                return $this->redirectToRoute('integrated_user_scope_index');
            }

            if ($form->isValid()) {
                $scope = $form->getData();

                $this->scopeManager->persist($scope);
                $this->addFlash('success', \sprintf('The scope %s is created', $scope->getName()));

                return $this->redirectToRoute('integrated_user_scope_index');
            }
        }

        return $this->render('@IntegratedUser/scope/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Scope $scope, Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        /** @var Form $form */
        $form = $this->createEditForm($scope);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_scope_index');
            }

            if ($form->isValid()) {
                $this->scopeManager->persist($scope);
                $this->addFlash('success', \sprintf('The changes to the scope %s are saved', $scope->getName()));

                return $this->redirectToRoute('integrated_user_scope_index');
            }
        }

        return $this->render('@IntegratedUser/scope/edit.html.twig', [
            'scope' => $scope,
            'form' => $form,
        ]);
    }

    public function delete(Scope $scope, Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$scope || $scope->isAdmin()) {
            return $this->redirectToRoute('integrated_user_scope_index');
        }

        /** @var Form $form */
        $form = $this->createDeleteForm($scope);

        if ($request->isMethod('delete')) {
            $form->handleRequest($request);

            // check for cancel click else it's a submit
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_scope_index');
            }

            $hasRelations = false;

            if ($this->documentManager->getRepository(Channel::class)->findBy(['scope' => (string) $scope->getId()])) {
                $form->addError(
                    new FormError('This scope is in use by channels.')
                );

                $hasRelations = true;
            }

            if ($this->entityManager->getRepository(User::class)->findBy(['scope' => $scope])) {
                $form->addError(
                    new FormError('This scope is in use by users.')
                );

                $hasRelations = true;
            }

            if (false === $hasRelations) {
                $this->scopeManager->remove($scope);
                $this->addFlash('success', \sprintf('The scope %s is removed', $scope->getName()));

                return $this->redirectToRoute('integrated_user_scope_index');
            }
        }

        return $this->render('@IntegratedUser/scope/delete.html.twig', [
            'scope' => $scope,
            'form' => $form,
        ]);
    }

    private function createNewForm(): FormInterface
    {
        $form = $this->createForm(ScopeFormType::class, null, [
            'action' => $this->generateUrl('integrated_user_scope_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    private function createEditForm(Scope $scope): FormInterface
    {
        $form = $this->createForm(ScopeFormType::class, $scope, [
            'action' => $this->generateUrl('integrated_user_scope_edit', ['id' => $scope->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    private function createDeleteForm(Scope $scope): FormInterface
    {
        $form = $this->createForm(DeleteFormType::class, $scope, [
            'action' => $this->generateUrl('integrated_user_scope_delete', ['id' => $scope->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }
}
