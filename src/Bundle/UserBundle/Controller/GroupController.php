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

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\IntegratedBundle\Controller\PaginationQueryTrait;
use Integrated\Bundle\UserBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\UserBundle\Form\Type\GroupFormType;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
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
    private UserManagerInterface $userManager;
    private PaginatorInterface $paginator;
    private EntityManagerInterface $entityManager;

    public function __construct(GroupManagerInterface $manager, UserManagerInterface $userManager, PaginatorInterface $paginator, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->userManager = $userManager;
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
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
        $groups = $this->extractGroupItemsFromPaginator($paginator);
        $groupIds = [];
        foreach ($groups as $group) {
            if ($group instanceof GroupInterface) {
                $groupIds[] = (int) $group->getId();
            }
        }

        return $this->render('@IntegratedUser/group/index.html.twig', [
            'groups' => $paginator,
            'userCountsByGroupId' => $this->resolveUserCountsByGroupIds($groupIds),
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

        $availableUsers = $this->resolveAssignableUsers();
        $selectedGroupUserIds = $this->resolveUserIdsByGroupId((int) $group->getId());

        $form = $this->createEditForm($group);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getClickedButton()?->getName() === 'cancel') {
                return $this->redirectToRoute('integrated_user_group_index');
            }

            $selectedGroupUserIds = $this->normalizeSelectedUserIds($request->request->all('group_user_ids'));

            if ($form->isValid()) {
                if (!$this->canManageAdminPrivileges() && $this->groupHasAdminRole($group)) {
                    $errorTarget = $form->has('roles') ? $form->get('roles') : $form;
                    $errorTarget->addError(new FormError('You are not allowed to assign the administrator role.'));

                    return $this->render('@IntegratedUser/group/edit.html.twig', [
                        'group' => $group,
                        'form' => $form,
                        'availableUsers' => $availableUsers,
                        'selectedGroupUserIds' => $selectedGroupUserIds,
                    ]);
                }

                $this->syncGroupUsers($group, $selectedGroupUserIds);
                $this->manager->persist($group);
                $this->addFlash('success', \sprintf('The changes to the group %s are saved', $group->getName()));

                return $this->redirectToRoute('integrated_user_group_index');
            }
        }

        return $this->render('@IntegratedUser/group/edit.html.twig', [
            'group' => $group,
            'form' => $form,
            'availableUsers' => $availableUsers,
            'selectedGroupUserIds' => $selectedGroupUserIds,
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

    /**
     * @param mixed $paginator
     *
     * @return array<int, GroupInterface>
     */
    private function extractGroupItemsFromPaginator($paginator): array
    {
        if (\is_object($paginator) && method_exists($paginator, 'getItems')) {
            $items = $paginator->getItems();
        } else {
            $items = $paginator;
        }

        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items, false);
        }

        if (!\is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, static fn ($item): bool => $item instanceof GroupInterface));
    }

    /**
     * @param list<int> $groupIds
     *
     * @return array<int, int>
     */
    private function resolveUserCountsByGroupIds(array $groupIds): array
    {
        $groupIds = array_values(array_unique(array_filter(array_map(static fn (int $id): int => $id, $groupIds), static fn (int $id): bool => $id > 0)));
        if ($groupIds === []) {
            return [];
        }

        $countsByGroupId = [];
        foreach ($groupIds as $groupId) {
            $countsByGroupId[$groupId] = 0;
        }

        $parameterType = class_exists(ArrayParameterType::class) ? ArrayParameterType::INTEGER : Connection::PARAM_INT_ARRAY;
        $rows = $this->entityManager->getConnection()->executeQuery(
            'SELECT ug.group_id, COUNT(ug.user_id) AS user_count
             FROM security_user_groups ug
             WHERE ug.group_id IN (:groupIds)
             GROUP BY ug.group_id',
            ['groupIds' => $groupIds],
            ['groupIds' => $parameterType]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $groupId = (int) ($row['group_id'] ?? 0);
            if (!isset($countsByGroupId[$groupId])) {
                continue;
            }

            $countsByGroupId[$groupId] = (int) ($row['user_count'] ?? 0);
        }

        return $countsByGroupId;
    }

    /**
     * @return list<array{id:int,username:string}>
     */
    private function resolveUsersByGroupId(int $groupId): array
    {
        if ($groupId <= 0) {
            return [];
        }

        $userClass = $this->userManager->getClassName();
        $tableName = $this->entityManager->getConnection()->quoteIdentifier(
            $this->entityManager->getClassMetadata($userClass)->getTableName()
        );
        $rows = $this->entityManager->getConnection()->executeQuery(
            sprintf(
                'SELECT u.id AS user_id, u.username
                 FROM security_user_groups ug
                 INNER JOIN %s u ON u.id = ug.user_id
                 WHERE ug.group_id = :groupId
                 ORDER BY u.username ASC',
                $tableName
            ),
            ['groupId' => $groupId]
        )->fetchAllAssociative();

        $users = [];
        foreach ($rows as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $users[] = [
                'id' => $userId,
                'username' => (string) ($row['username'] ?? ''),
            ];
        }

        return $users;
    }

    /**
     * @return list<array{id:int,username:string,enabled:bool}>
     */
    private function resolveAssignableUsers(): array
    {
        $userClass = $this->userManager->getClassName();
        $users = $this->entityManager->getRepository($userClass)->createQueryBuilder('User')
            ->select('User')
            ->orderBy('User.username', 'ASC')
            ->getQuery()
            ->getResult();

        $rows = [];
        foreach ($users as $user) {
            if (!$user instanceof UserInterface) {
                continue;
            }

            $rows[] = [
                'id' => (int) $user->getId(),
                'username' => (string) $user->getUsername(),
                'enabled' => $user->isEnabled(),
            ];
        }

        return $rows;
    }

    /**
     * @return list<int>
     */
    private function resolveUserIdsByGroupId(int $groupId): array
    {
        $users = $this->resolveUsersByGroupId($groupId);
        $userIds = [];
        foreach ($users as $user) {
            $userId = (int) ($user['id'] ?? 0);
            if ($userId > 0) {
                $userIds[] = $userId;
            }
        }

        return $userIds;
    }

    /**
     * @param mixed $rawUserIds
     *
     * @return list<int>
     */
    private function normalizeSelectedUserIds($rawUserIds): array
    {
        if (!\is_array($rawUserIds)) {
            return [];
        }

        $normalized = [];
        foreach ($rawUserIds as $rawUserId) {
            $userId = (int) $rawUserId;
            if ($userId > 0) {
                $normalized[$userId] = $userId;
            }
        }

        return array_values($normalized);
    }

    /**
     * @param list<int> $selectedUserIds
     */
    private function syncGroupUsers(GroupInterface $group, array $selectedUserIds): void
    {
        $groupId = (int) $group->getId();
        if ($groupId <= 0) {
            return;
        }

        $currentUserIds = $this->resolveUserIdsByGroupId($groupId);
        $selectedUserIds = $this->normalizeSelectedUserIds($selectedUserIds);

        $userIdsToAdd = array_values(array_diff($selectedUserIds, $currentUserIds));
        $userIdsToRemove = array_values(array_diff($currentUserIds, $selectedUserIds));
        if ($userIdsToAdd === [] && $userIdsToRemove === []) {
            return;
        }

        $allAffectedUserIds = array_values(array_unique(array_merge($userIdsToAdd, $userIdsToRemove)));
        $usersById = [];
        foreach ($this->resolveUsersByIds($allAffectedUserIds) as $user) {
            $usersById[(int) $user->getId()] = $user;
        }

        foreach ($userIdsToAdd as $userId) {
            if (!isset($usersById[$userId])) {
                continue;
            }

            $usersById[$userId]->addGroup($group);
            $this->entityManager->persist($usersById[$userId]);
        }

        foreach ($userIdsToRemove as $userId) {
            if (!isset($usersById[$userId])) {
                continue;
            }

            $usersById[$userId]->removeGroup($group);
            $this->entityManager->persist($usersById[$userId]);
        }

        $this->entityManager->flush();
    }

    /**
     * @param list<int> $userIds
     *
     * @return list<UserInterface>
     */
    private function resolveUsersByIds(array $userIds): array
    {
        $userIds = $this->normalizeSelectedUserIds($userIds);
        if ($userIds === []) {
            return [];
        }

        $userClass = $this->userManager->getClassName();
        $users = $this->entityManager->getRepository($userClass)->createQueryBuilder('User')
            ->select('User')
            ->where('User.id IN (:userIds)')
            ->setParameter('userIds', $userIds)
            ->getQuery()
            ->getResult();

        return array_values(array_filter($users, static fn ($user): bool => $user instanceof UserInterface));
    }
}
