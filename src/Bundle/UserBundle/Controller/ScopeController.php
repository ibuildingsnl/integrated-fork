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

use Doctrine\ORM\EntityRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\ScopeFormType;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\ScopeManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Michael Jongman <michael@e-active.nl>
 */
class ScopeController extends AbstractController
{
    public function __construct(
        private readonly ScopeManagerInterface $scopeManager,
        private readonly ChannelRepository $channelRepository,
        private readonly EntityRepository $userRepository,
    ) {
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $paginator = $this->getPaginator()->paginate(
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

        $form = $this->createNewForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->get('cancel')->isClicked()) {
                return $this->redirectToRoute('integrated_user_scope_index');
            }

            if ($form->isValid()) {
                $scope = $form->getData();

                $this->scopeManager->persist($scope);
                $this->addFlash('success', sprintf('The scope %s is created', $scope->getName()));

                return $this->redirectToRoute('integrated_user_scope_index');
            }
        }

        return $this->render('@IntegratedUser/scope/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** @throws NotFoundHttpException */
    public function edit(Scope $scope, Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createEditForm($scope);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->get('cancel')->isClicked()) {
                return $this->redirectToRoute('integrated_user_scope_index');
            }

            if ($form->isValid()) {
                $this->scopeManager->persist($scope);
                $this->addFlash('success', sprintf('The changes to the scope %s are saved', $scope->getName()));

                return $this->redirectToRoute('integrated_user_scope_index');
            }
        }

        return $this->render('@IntegratedUser/scope/edit.html.twig', [
            'scope' => $scope,
            'form' => $form->createView(),
        ]);
    }

    public function delete(Scope $scope, Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($scope->isAdmin()) {
            return $this->redirectToRoute('integrated_user_scope_index');
        }

        $form = $this->createDeleteForm($scope);

        if ($request->isMethod('delete')) {
            $form->handleRequest($request);

            // check for cancel click else its a submit
            if ($form->get('actions')->get('cancel')->isClicked()) {
                return $this->redirectToRoute('integrated_user_scope_index');
            }

            $hasRelations = false;

            if ($this->channelRepository->findBy(['scope' => $scope->getId()])) {
                $form->addError(
                    new FormError('This scope is in use by channels.')
                );

                $hasRelations = true;
            }

            if ($this->userRepository->findBy(['scope' => $scope])) {
                $form->addError(
                    new FormError('This scope is in use by users.')
                );

                $hasRelations = true;
            }

            if (false === $hasRelations) {
                $this->scopeManager->remove($scope);
                $this->addFlash('success', sprintf('The scope %s is removed', $scope->getName()));

                return $this->redirectToRoute('integrated_user_scope_index');
            }
        }

        return $this->render('@IntegratedUser/scope/delete.html.twig', [
            'scope' => $scope,
            'form' => $form->createView(),
        ]);
    }

    protected function createNewForm(): FormInterface
    {
        $form = $this->createForm(
            ScopeFormType::class,
            null,
            [
                'action' => $this->generateUrl('integrated_user_scope_new'),
                'method' => 'POST',
            ]
        );

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    protected function createEditForm(Scope $scope): FormInterface
    {
        $form = $this->createForm(
            ScopeFormType::class,
            $scope,
            [
                'action' => $this->generateUrl('integrated_user_scope_edit', ['id' => $scope->getId()]),
                'method' => 'PUT',
            ]
        );

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    protected function createDeleteForm(Scope $scope): FormInterface
    {
        $form = $this->createForm(
            DeleteFormType::class,
            $scope,
            [
                'action' => $this->generateUrl('integrated_user_scope_delete', ['id' => $scope->getId()]),
                'method' => 'DELETE',
            ]
        );

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }
}
