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
use Integrated\Bundle\IntegratedBundle\Controller\PaginationQueryTrait;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\UserFilterType;
use Integrated\Bundle\UserBundle\Form\Type\UserFormType;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Integrated\Bundle\UserBundle\Model\ScopeManagerInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\UserBundle\Service\BulkUserActionService;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    use PaginationQueryTrait;

    private UserManagerInterface $manager;
    private FilterQueryProvider $provider;
    private PaginatorInterface $paginator;
    private LoggerInterface $logger;
    private GroupManagerInterface $groupManager;
    private ScopeManagerInterface $scopeManager;
    private BulkUserActionService $bulkUserActionService;

    public function __construct(
        UserManagerInterface $manager,
        FilterQueryProvider $provider,
        PaginatorInterface $paginator,
        LoggerInterface $logger,
        GroupManagerInterface $groupManager,
        ScopeManagerInterface $scopeManager,
        BulkUserActionService $bulkUserActionService,
    ) {
        $this->manager = $manager;
        $this->provider = $provider;
        $this->paginator = $paginator;
        $this->logger = $logger;
        $this->groupManager = $groupManager;
        $this->scopeManager = $scopeManager;
        $this->bulkUserActionService = $bulkUserActionService;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $data = $request->query->all('integrated_user_filter');
        $sort = [
            'field' => (string) $request->query->get('sort', 'createdAt'),
            'direction' => (string) $request->query->get('direction', 'desc'),
        ];

        $users = $this->provider->getUsers($data, $sort);

        $facetFilter = $this->createForm(UserFilterType::class, null, [
            'data' => $data,
        ]);
        $facetFilter->handleRequest($request);

        $pagination = $this->paginator->paginate(
            $users,
            $this->getPositiveIntQueryParameter($request, 'page', 1),
            15
        );

        return $this->render('@IntegratedUser/user/index.html.twig', [
            'users' => $pagination,
            'facetFilter' => $facetFilter,
            'allGroups' => $this->filterAssignableGroups($this->groupManager->findAll()),
            'allScopes' => $this->scopeManager->findAll(),
            'bulkActions' => [
                BulkUserActionService::ACTION_ENABLE_LOGIN => 'Enable login',
                BulkUserActionService::ACTION_DISABLE_LOGIN => 'Disable login',
                BulkUserActionService::ACTION_ASSIGN_GROUP => 'Assign group',
                BulkUserActionService::ACTION_CHANGE_SCOPE => 'Change scope',
                BulkUserActionService::ACTION_RESET_2FA => 'Reset 2FA',
            ],
        ]);
    }

    public function bulk(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('user_bulk_action', $token)) {
            $this->addFlash('danger', 'Invalid bulk action token.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        $action = (string) $request->request->get('bulk_action', '');
        $selectedValues = $request->request->all('user_ids');
        $selectedIds = array_values(array_filter(array_map(
            static fn ($id): string => \is_scalar($id) ? (string) $id : '',
            $selectedValues
        ), static fn (string $id): bool => $id !== ''));
        if ($action === '' || $selectedIds === []) {
            $this->addFlash('warning', 'Select at least one user and a bulk action.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        $users = [];
        foreach ($selectedIds as $id) {
            $user = $this->manager->find($id);
            if ($user instanceof UserInterface) {
                $users[] = $user;
            }
        }

        if ($users === []) {
            $this->addFlash('warning', 'No valid users selected.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        if (!$this->canManageAdminPrivileges()) {
            foreach ($users as $user) {
                if ($this->userHasAdminPrivileges($user)) {
                    $this->addFlash('danger', 'You are not allowed to manage administrator accounts.');

                    return $this->redirectToRoute('integrated_user_user_index');
                }
            }
        }

        $group = null;
        $scope = null;

        if ($action === BulkUserActionService::ACTION_ASSIGN_GROUP) {
            $groupId = $request->request->get('bulk_group');
            $group = $groupId ? $this->groupManager->find($groupId) : null;
            if ($group === null) {
                $this->addFlash('warning', 'Select a group for this bulk action.');

                return $this->redirectToRoute('integrated_user_user_index');
            }
            if (!$this->canManageAdminPrivileges() && $this->groupHasAdminRole($group)) {
                $this->addFlash('danger', 'You are not allowed to assign administrator groups.');

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        if ($action === BulkUserActionService::ACTION_CHANGE_SCOPE) {
            $scopeId = $request->request->get('bulk_scope');
            $scope = $scopeId ? $this->scopeManager->find($scopeId) : null;
            if ($scope === null) {
                $this->addFlash('warning', 'Select a scope for this bulk action.');

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        $updated = $this->bulkUserActionService->apply($users, $action, $group, $scope);

        foreach ($users as $user) {
            $this->manager->persist($user);
        }

        $this->logger->info('Bulk user action executed', [
            'actor' => $this->getUser()?->getUserIdentifier(),
            'action' => $action,
            'selected_ids' => $selectedIds,
            'updated_count' => $updated,
        ]);

        $this->addFlash('success', \sprintf('Bulk action applied to %d user(s).', $updated));

        return $this->redirectToRoute('integrated_user_user_index');
    }

    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createNewForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $clickedButton = $form instanceof Form ? $form->getClickedButton() : null;
            if (\is_object($clickedButton) && method_exists($clickedButton, 'getName') && $clickedButton->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                $user = $form->getData();
                if (!$this->canManageAdminPrivileges() && $this->userHasAdminPrivileges($user)) {
                    $errorTarget = $form->has('groups') ? $form->get('groups') : $form;
                    $errorTarget->addError(new FormError('You are not allowed to assign administrator groups.'));

                    return $this->render('@IntegratedUser/user/new.html.twig', [
                        'form' => $form,
                    ]);
                }

                $this->manager->persist($user);
                $this->logger->info('User created', [
                    'actor' => $this->getUser()?->getUserIdentifier(),
                    'target_user_id' => $user->getId(),
                    'target_username' => $user->getUserIdentifier(),
                ]);
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

        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $clickedButton = $form instanceof Form ? $form->getClickedButton() : null;
            if (\is_object($clickedButton) && method_exists($clickedButton, 'getName') && $clickedButton->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                if (!$this->canManageAdminPrivileges() && $this->userHasAdminPrivileges($user)) {
                    $errorTarget = $form->has('groups') ? $form->get('groups') : $form;
                    $errorTarget->addError(new FormError('You are not allowed to assign administrator groups.'));

                    return $this->render('@IntegratedUser/user/edit.html.twig', [
                        'user' => $user,
                        'form' => $form,
                    ]);
                }

                $this->manager->persist($user);
                $this->logger->info('User updated', [
                    'actor' => $this->getUser()?->getUserIdentifier(),
                    'target_user_id' => $user->getId(),
                    'target_username' => $user->getUserIdentifier(),
                ]);
                $this->addFlash('success', \sprintf('The changes to the user %s are saved', $user->getUserIdentifier()));

                return $this->redirectToRoute('integrated_user_user_edit', ['id' => $user->getId()]);
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

        if (!$this->canManageAdminPrivileges() && $this->userHasAdminPrivileges($user)) {
            $this->addFlash('danger', 'You are not allowed to manage administrator accounts.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $clickedButton = $form instanceof Form ? $form->getClickedButton() : null;
            if (\is_object($clickedButton) && method_exists($clickedButton, 'getName') && $clickedButton->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                if ($user->isEnabled()) {
                    $user->setEnabled(false);
                    $this->manager->persist($user);
                    $this->logger->info('User deactivated', [
                        'actor' => $this->getUser()?->getUserIdentifier(),
                        'target_user_id' => $user->getId(),
                        'target_username' => $user->getUserIdentifier(),
                    ]);
                    $this->addFlash('success', \sprintf('The user %s is deactivated', $user->getUserIdentifier()));
                } else {
                    $this->addFlash('info', \sprintf('The user %s was already deactivated', $user->getUserIdentifier()));
                }

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        return $this->render('@IntegratedUser/user/delete.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    public function enable(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$request->isMethod('POST')) {
            throw $this->createNotFoundException();
        }

        $token = (string) $request->request->get('enable_token', '');
        if (!$this->isCsrfTokenValid('user_enable', $token)) {
            $this->addFlash('danger', 'Invalid request token.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        $user = $this->manager->find($request->get('id'));
        if (!$user) {
            return $this->redirectToRoute('integrated_user_user_index');
        }

        if (!$this->canManageAdminPrivileges() && $this->userHasAdminPrivileges($user)) {
            $this->addFlash('danger', 'You are not allowed to manage administrator accounts.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        if (!$user->isEnabled()) {
            $user->setEnabled(true);
            $this->manager->persist($user);
            $this->logger->info('User enabled', [
                'actor' => $this->getUser()?->getUserIdentifier(),
                'target_user_id' => $user->getId(),
                'target_username' => $user->getUserIdentifier(),
            ]);
            $this->addFlash('success', \sprintf('The user %s is enabled', $user->getUserIdentifier()));
        } else {
            $this->addFlash('info', \sprintf('The user %s is already enabled', $user->getUserIdentifier()));
        }

        return $this->redirectToRoute('integrated_user_user_index');
    }

    public function deleteAccount(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->manager->find($request->get('id'));
        if (!$user) {
            return $this->redirectToRoute('integrated_user_user_index');
        }

        $currentUser = $this->getUser();
        if ($currentUser instanceof UserInterface && $currentUser->getId() === $user->getId()) {
            $this->addFlash('danger', 'You cannot delete your own account.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        if (!$this->canManageAdminPrivileges() && $this->userHasAdminPrivileges($user)) {
            $this->addFlash('danger', 'You are not allowed to manage administrator accounts.');

            return $this->redirectToRoute('integrated_user_user_index');
        }

        /** @var FormInterface<mixed> $form */
        $form = $this->createDeleteAccountForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $clickedButton = $form instanceof Form ? $form->getClickedButton() : null;
            if (\is_object($clickedButton) && method_exists($clickedButton, 'getName') && $clickedButton->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_user_index');
            }

            if ($form->isValid()) {
                $this->manager->remove($user);
                $this->logger->info('User permanently deleted', [
                    'actor' => $this->getUser()?->getUserIdentifier(),
                    'target_user_id' => $user->getId(),
                    'target_username' => $user->getUserIdentifier(),
                ]);
                $this->addFlash('success', \sprintf('The account %s has been permanently deleted', $user->getUserIdentifier()));

                return $this->redirectToRoute('integrated_user_user_index');
            }
        }

        return $this->render('@IntegratedUser/user/delete_account.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /** @return FormInterface<mixed> */
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

    /** @return FormInterface<mixed> */
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

    /** @return FormInterface<mixed> */
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

    /** @return FormInterface<mixed> */
    protected function createDeleteAccountForm(UserInterface $user): FormInterface
    {
        if (!$this->isGranted('ROLE_USER_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(DeleteFormType::class, $user, [
            'action' => $this->generateUrl('integrated_user_user_delete_account', ['id' => $user->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }

    private function canManageAdminPrivileges(): bool
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    private function userHasAdminPrivileges(UserInterface $user): bool
    {
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        foreach ($user->getGroups() as $group) {
            if ($this->groupHasAdminRole($group)) {
                return true;
            }
        }

        return false;
    }

    private function groupHasAdminRole(mixed $group): bool
    {
        if (!$group instanceof GroupInterface && (!\is_object($group) || !method_exists($group, 'getRoles'))) {
            return false;
        }

        $roles = $group->getRoles();

        return \is_array($roles) && \in_array('ROLE_ADMIN', $roles, true);
    }

    /**
     * @param array<int, mixed> $groups
     *
     * @return array<int, mixed>
     */
    private function filterAssignableGroups(array $groups): array
    {
        if ($this->canManageAdminPrivileges()) {
            return $groups;
        }

        return array_values(array_filter($groups, fn ($group): bool => !$this->groupHasAdminRole($group)));
    }
}
