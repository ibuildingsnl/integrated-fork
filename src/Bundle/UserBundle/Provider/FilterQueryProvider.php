<?php

namespace Integrated\Bundle\UserBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;

class FilterQueryProvider
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param array|null $data
     * @param array{field?: string, direction?: string} $sort
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsers($data, array $sort = [])
    {
        $data = \is_array($data) ? $data : [];
        $queryBuilder = $this->userManager->createQueryBuilder()->select('User');

        if (isset($data['groups'])) {
            $groups = array_filter((array) $data['groups']);
            if ($groups !== []) {
                $queryBuilder
                    ->leftJoin('User.groups', 'Groups')
                    ->where('Groups IN (:groups)')
                    ->setParameter('groups', $groups);
            }
        }

        if (isset($data['scope'])) {
            $scope = array_filter((array) $data['scope']);
            if ($scope !== []) {
                $queryBuilder
                    ->leftJoin('User.scope', 'Scope')
                    ->andWhere('Scope IN (:scope)')
                    ->setParameter('scope', $scope);
            }
        }

        if (isset($data['q']) && trim((string) $data['q']) !== '') {
            $queryBuilder
                ->andWhere('User.username LIKE :q OR User.email LIKE :q')
                ->setParameter('q', '%'.trim((string) $data['q']).'%');
        }

        if (isset($data['roles'])) {
            $roles = array_values(array_filter(array_map(
                static fn (mixed $value): string => \is_scalar($value) ? trim((string) $value) : '',
                (array) $data['roles']
            )));

            if ($roles !== []) {
                $queryBuilder
                    ->andWhere(
                        'EXISTS (SELECT 1 FROM Integrated\Bundle\UserBundle\Model\Role role_direct_selected WHERE role_direct_selected MEMBER OF User.roles AND role_direct_selected.role IN (:roles)) OR EXISTS (SELECT 1 FROM Integrated\Bundle\UserBundle\Model\Group group_selected JOIN group_selected.roles role_group_selected WHERE group_selected MEMBER OF User.groups AND role_group_selected.role IN (:roles))'
                    )
                    ->setParameter('roles', $roles);
            }
        }

        if (isset($data['has_relation']) && \is_array($data['has_relation'])) {
            $selected = array_values(array_filter($data['has_relation'], static fn ($value) => $value === '0' || $value === '1'));

            if (\count($selected) === 1) {
                if ($selected[0] === '1') {
                    $queryBuilder
                        ->andWhere('User.relation IS NOT NULL')
                        ->andWhere("User.relation <> ''");
                } else {
                    $queryBuilder->andWhere("(User.relation IS NULL OR User.relation = '')");
                }
            }
        }

        $sortField = $this->normalizeSortField($sort['field'] ?? null);
        $sortDirection = $this->normalizeSortDirection($sort['direction'] ?? null);

        if ($sortField === 'scope.name') {
            $queryBuilder
                ->leftJoin('User.scope', 'ScopeSort')
                ->addOrderBy('ScopeSort.name', $sortDirection);
        } elseif ($sortField === 'role') {
            $queryBuilder
                ->addSelect(
                    "COALESCE((SELECT MIN(role_direct.role) FROM Integrated\Bundle\UserBundle\Model\Role role_direct WHERE role_direct MEMBER OF User.roles), (SELECT MIN(role_group.role) FROM Integrated\Bundle\UserBundle\Model\Group group_item JOIN group_item.roles role_group WHERE group_item MEMBER OF User.groups), '') AS HIDDEN sort_role"
                )
                ->addOrderBy('sort_role', $sortDirection);
        } else {
            $queryBuilder->addOrderBy('User.'.$sortField, $sortDirection);
        }

        if ($sortField !== 'id') {
            $queryBuilder->addOrderBy('User.id', 'ASC');
        }

        return $queryBuilder->getQuery();
    }

    private function normalizeSortField(mixed $value): string
    {
        $field = trim((string) $value);
        if (!in_array($field, ['createdAt', 'username', 'scope.name', 'id', 'role'], true)) {
            return 'createdAt';
        }

        return $field;
    }

    private function normalizeSortDirection(mixed $value): string
    {
        return strtolower(trim((string) $value)) === 'asc' ? 'ASC' : 'DESC';
    }

    public function getGroupChoices($data)
    {
        $sql = 'SELECT s.id, s.name, count(g.group_id) as count
            FROM security_groups s
            INNER JOIN security_user_groups g ON s.id = g.group_id
            INNER JOIN security_users u ON g.user_id = u.id
            WHERE (:scope <= 0 OR u.scope = :scope)
            AND (:groups <= 0 OR s.id IN (:groups))
            GROUP BY g.group_id HAVING count > 0
        ';

        /** @var EntityManagerInterface $manager */
        $manager = $this->userManager->getObjectManager();

        return $this->formatChoices($manager->createNativeQuery($sql, $this->getMapping()), $data);
    }

    public function getScopeChoices($data)
    {
        $sql = 'SELECT s.id, s.name, count(DISTINCT u.id) as count
            FROM security_scopes s
            INNER JOIN security_users u ON s.id = u.scope
            LEFT JOIN security_user_groups g ON u.id = g.user_id
            WHERE (:scope <= 0 OR u.scope = :scope)
            AND (:groups <= 0 OR g.group_id IN (:groups))
            GROUP BY u.scope
        ';

        /** @var EntityManagerInterface $manager */
        $manager = $this->userManager->getObjectManager();

        return $this->formatChoices($manager->createNativeQuery($sql, $this->getMapping()), $data);
    }

    public function getRoleChoices($data = []): array
    {
        $data = \is_array($data) ? $data : [];

        $sql = 'SELECT
                r.name AS role,
                COALESCE(NULLIF(r.label, \'\'), r.name) AS label,
                COUNT(DISTINCT u.id) AS count
            FROM security_roles r
            LEFT JOIN (
                SELECT ur.role_id AS role_id, ur.user_id AS user_id
                FROM security_user_roles ur
                UNION
                SELECT gr.role_id AS role_id, ug.user_id AS user_id
                FROM security_group_roles gr
                INNER JOIN security_user_groups ug ON ug.group_id = gr.group_id
            ) role_users ON role_users.role_id = r.id
            LEFT JOIN security_users u ON u.id = role_users.user_id
            LEFT JOIN security_user_groups user_groups ON user_groups.user_id = u.id
            WHERE (:scope <= 0 OR u.scope = :scope)
              AND (:groups <= 0 OR user_groups.group_id IN (:groups))
            GROUP BY r.id, r.name, r.label
            HAVING count > 0
            ORDER BY label ASC';

        /** @var EntityManagerInterface $manager */
        $manager = $this->userManager->getObjectManager();
        $query = $manager->createNativeQuery($sql, $this->getRoleMapping());
        $query->setParameter('scope', isset($data['scope']) ? $data['scope'] : 0);
        $query->setParameter('groups', isset($data['groups']) ? array_filter((array) $data['groups']) : 0);

        $choices = [];
        foreach ($query->getResult() as $result) {
            $role = trim((string) ($result['role'] ?? ''));
            if ($role === '') {
                continue;
            }

            $label = trim((string) ($result['label'] ?? ''));
            if ($label === '') {
                $label = $role;
            }

            $count = max(0, (int) ($result['count'] ?? 0));
            $choices[\sprintf('%s %d', $label, $count)] = $role;
        }

        return $choices;
    }

    private function getRoleMapping(): ResultSetMapping
    {
        $mapping = new ResultSetMapping();
        $mapping->addScalarResult('role', 'role');
        $mapping->addScalarResult('label', 'label');
        $mapping->addScalarResult('count', 'count');

        return $mapping;
    }

    private function getMapping()
    {
        $mapping = new ResultSetMapping();
        $mapping->addScalarResult('id', 'id');
        $mapping->addScalarResult('count', 'count');
        $mapping->addScalarResult('name', 'name');

        return $mapping;
    }

    private function formatChoices($query, $data)
    {
        $query->setParameter('scope', (isset($data['scope'])) ? $data['scope'] : 0);
        $query->setParameter('groups', (isset($data['groups'])) ? array_filter($data['groups']) : 0);

        $choices = [];
        foreach ($query->getResult() as $result) {
            $choices[\sprintf('%s %d', $result['name'], $result['count'])] = $result['id'];
        }

        return $choices;
    }
}
