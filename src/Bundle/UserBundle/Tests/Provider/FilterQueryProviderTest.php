<?php

namespace Integrated\Bundle\UserBundle\Tests\Provider;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\UserBundle\Provider\FilterQueryProvider;
use PHPUnit\Framework\TestCase;

class FilterQueryProviderTest extends TestCase
{
    public function testGetUsersSupportsRoleSorting(): void
    {
        $query = $this->createMock(Query::class);

        $addSelect = [];
        $orderBy = [];

        $builder = $this->createMock(QueryBuilder::class);
        $builder->method('select')->willReturnSelf();
        $builder->method('leftJoin')->willReturnSelf();
        $builder->method('where')->willReturnSelf();
        $builder->method('andWhere')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('addSelect')->willReturnCallback(function (string $expr) use (&$addSelect, $builder) {
            $addSelect[] = $expr;

            return $builder;
        });
        $builder->method('addOrderBy')->willReturnCallback(function (string $field, ?string $direction = null) use (&$orderBy, $builder) {
            $orderBy[] = [$field, $direction];

            return $builder;
        });
        $builder->method('getQuery')->willReturn($query);

        $manager = $this->createMock(UserManager::class);
        $manager->method('createQueryBuilder')->willReturn($builder);

        $provider = new FilterQueryProvider($manager);
        $result = $provider->getUsers([], ['field' => 'role', 'direction' => 'asc']);

        self::assertSame($query, $result);
        self::assertContains(
            "COALESCE((SELECT MIN(role_direct.role) FROM Integrated\Bundle\UserBundle\Model\Role role_direct WHERE role_direct MEMBER OF User.roles), (SELECT MIN(role_group.role) FROM Integrated\Bundle\UserBundle\Model\Group group_item JOIN group_item.roles role_group WHERE group_item MEMBER OF User.groups), '') AS HIDDEN sort_role",
            $addSelect
        );
        self::assertContains(['sort_role', 'ASC'], $orderBy);
    }

    public function testGetUsersAppliesSelectedRolesFilterAcrossDirectAndGroupRoles(): void
    {
        $query = $this->createMock(Query::class);

        $andWhere = [];
        $params = [];

        $builder = $this->createMock(QueryBuilder::class);
        $builder->method('select')->willReturnSelf();
        $builder->method('leftJoin')->willReturnSelf();
        $builder->method('where')->willReturnSelf();
        $builder->method('andWhere')->willReturnCallback(function (string $expr) use (&$andWhere, $builder) {
            $andWhere[] = $expr;

            return $builder;
        });
        $builder->method('setParameter')->willReturnCallback(function (string $name, mixed $value) use (&$params, $builder) {
            $params[$name] = $value;

            return $builder;
        });
        $builder->method('addOrderBy')->willReturnSelf();
        $builder->method('addSelect')->willReturnSelf();
        $builder->method('getQuery')->willReturn($query);

        $manager = $this->createMock(UserManager::class);
        $manager->method('createQueryBuilder')->willReturn($builder);

        $provider = new FilterQueryProvider($manager);
        $result = $provider->getUsers(['roles' => ['ROLE_ADMIN', 'ROLE_USER']]);

        self::assertSame($query, $result);
        self::assertContains(
            'EXISTS (SELECT 1 FROM Integrated\Bundle\UserBundle\Model\Role role_direct_selected WHERE role_direct_selected MEMBER OF User.roles AND role_direct_selected.role IN (:roles)) OR EXISTS (SELECT 1 FROM Integrated\Bundle\UserBundle\Model\Group group_selected JOIN group_selected.roles role_group_selected WHERE group_selected MEMBER OF User.groups AND role_group_selected.role IN (:roles))',
            $andWhere
        );
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $params['roles'] ?? null);
    }
}
