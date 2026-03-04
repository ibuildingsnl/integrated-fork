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

        return $this->render('@IntegratedUser/group/index.html.twig', [
            'groups' => $paginator,
            'userCountsByGroupId' => $this->resolveUserCountsByGroups($groups),
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
        $selectedGroupUserIds = $this->resolveUserIdsByGroup($group);

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

        return \in_array('ROLE_ADMIN', $roles, true);
    }

    /**
     * @return list<GroupInterface>
     */
    private function extractGroupItemsFromPaginator(mixed $paginator): array
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
     * @param list<GroupInterface> $groups
     *
     * @return array<string, int>
     */
    private function resolveUserCountsByGroups(array $groups): array
    {
        $countsByGroupId = [];
        $trackedGroups = [];

        foreach ($groups as $group) {
            $groupId = $this->normalizeIdentifier($group->getId());
            if ('' === $groupId) {
                continue;
            }

            $countsByGroupId[$groupId] = 0;
            $trackedGroups[$groupId] = $group;
        }

        if ([] === $trackedGroups) {
            return [];
        }

        foreach ($this->resolveUsersForGroups(array_values($trackedGroups)) as $user) {
            foreach ($user->getGroups() as $group) {
                $groupId = $this->normalizeIdentifier($group->getId());
                if ('' === $groupId || !isset($countsByGroupId[$groupId])) {
                    continue;
                }

                ++$countsByGroupId[$groupId];
            }
        }

        return $countsByGroupId;
    }

    /**
     * @param list<GroupInterface> $groups
     *
     * @return list<UserInterface>
     */
    private function resolveUsersForGroups(array $groups): array
    {
        if ([] === $groups) {
            return [];
        }

        $queryContext = $this->resolveUserGroupAssociationContext();
        if (null === $queryContext) {
            return [];
        }

        $users = $this->entityManager->getRepository($queryContext['class'])->createQueryBuilder('User')
            ->select('User')
            ->innerJoin('User.'.$queryContext['groupAssociationField'], 'UserGroup')
            ->where('UserGroup IN (:groups)')
            ->setParameter('groups', $groups)
            ->getQuery()
            ->getResult();

        $uniqueUsers = [];
        foreach ($users as $user) {
            if (!$user instanceof UserInterface) {
                continue;
            }

            $userId = $this->normalizeIdentifier($user->getId());
            if ('' === $userId) {
                continue;
            }

            $uniqueUsers[$userId] = $user;
        }

        return array_values($uniqueUsers);
    }

    /**
     * @return list<array{id:string,username:string,enabled:bool}>
     */
    private function resolveAssignableUsers(): array
    {
        $queryContext = $this->resolveUserGroupAssociationContext();
        if (null === $queryContext) {
            return [];
        }

        $users = $this->entityManager->getRepository($queryContext['class'])->createQueryBuilder('User')
            ->select('User')
            ->getQuery()
            ->getResult();

        /** @var list<array{id:string,username:string,enabled:bool}> $rows */
        $rows = [];
        foreach ($users as $user) {
            if (!$user instanceof UserInterface) {
                continue;
            }

            $userId = $this->normalizeIdentifier($user->getId());
            if ('' === $userId) {
                continue;
            }

            $rows[] = [
                'id' => $userId,
                'username' => (string) $user->getUserIdentifier(),
                'enabled' => $user->isEnabled(),
            ];
        }

        usort($rows, static fn (array $left, array $right): int => strcasecmp($left['username'], $right['username']));

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function resolveUserIdsByGroup(GroupInterface $group): array
    {
        $userIds = [];
        foreach ($this->resolveUsersForGroups([$group]) as $user) {
            if (!$user->hasGroup($group)) {
                continue;
            }

            $userId = $this->normalizeIdentifier($user->getId());
            if ('' === $userId) {
                continue;
            }

            $userIds[$userId] = $userId;
        }

        return array_values($userIds);
    }

    /**
     * @return list<string>
     */
    private function normalizeSelectedUserIds(mixed $rawUserIds): array
    {
        if (!\is_array($rawUserIds)) {
            return [];
        }

        $normalized = [];
        foreach ($rawUserIds as $rawUserId) {
            $userId = $this->normalizeIdentifier($rawUserId);
            if ('' === $userId) {
                continue;
            }

            $normalized[$userId] = $userId;
        }

        return array_values($normalized);
    }

    /**
     * @param list<string> $selectedUserIds
     */
    private function syncGroupUsers(GroupInterface $group, array $selectedUserIds): void
    {
        $currentUserIds = $this->resolveUserIdsByGroup($group);
        $selectedUserIds = $this->normalizeSelectedUserIds($selectedUserIds);

        $userIdsToAdd = array_values(array_diff($selectedUserIds, $currentUserIds));
        $userIdsToRemove = array_values(array_diff($currentUserIds, $selectedUserIds));
        if ($userIdsToAdd === [] && $userIdsToRemove === []) {
            return;
        }

        $allAffectedUserIds = array_values(array_unique(array_merge($userIdsToAdd, $userIdsToRemove)));
        $usersById = [];
        foreach ($this->resolveUsersByIds($allAffectedUserIds) as $user) {
            $userId = $this->normalizeIdentifier($user->getId());
            if ('' === $userId) {
                continue;
            }

            $usersById[$userId] = $user;
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
     * @param list<string> $userIds
     *
     * @return list<UserInterface>
     */
    private function resolveUsersByIds(array $userIds): array
    {
        $userIds = $this->normalizeSelectedUserIds($userIds);
        if ($userIds === []) {
            return [];
        }

        $users = [];
        foreach ($userIds as $userId) {
            $user = $this->userManager->find($userId);
            if (!$user instanceof UserInterface) {
                continue;
            }

            $users[] = $user;
        }

        return $users;
    }

    /**
     * @return array{class: class-string<object>, groupAssociationField: string}|null
     */
    private function resolveUserGroupAssociationContext(): ?array
    {
        $userClass = $this->userManager->getClassName();
        if (!class_exists($userClass)) {
            return null;
        }
        /** @var class-string<object> $userClass */
        $groupClass = $this->manager->getClassName();
        if (!class_exists($groupClass)) {
            return null;
        }

        $metadata = $this->entityManager->getClassMetadata($userClass);
        foreach ($metadata->getAssociationNames() as $associationField) {
            if (!$metadata->isCollectionValuedAssociation($associationField)) {
                continue;
            }
            if (!is_a($metadata->getAssociationTargetClass($associationField), $groupClass, true)) {
                continue;
            }

            return [
                'class' => $userClass,
                'groupAssociationField' => $associationField,
            ];
        }

        return null;
    }

    private function normalizeIdentifier(mixed $identifier): string
    {
        if (!\is_scalar($identifier)) {
            return '';
        }

        return trim((string) $identifier);
    }
}
