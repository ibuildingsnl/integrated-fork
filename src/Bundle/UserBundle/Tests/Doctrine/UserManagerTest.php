<?php

namespace Integrated\Bundle\UserBundle\Tests\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\UserBundle\Model\ScopeInterface;
use Integrated\Bundle\UserBundle\Model\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserManagerTest extends TestCase
{
    public function testFindEnabledByUsernameOrEmailAndScopeMatchesUsernameOrEmailForAdminScope(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('getOneOrNullResult')->willReturn(null);

        $where = [];
        $andWhere = [];
        $params = [];

        $builder = $this->createMock(QueryBuilder::class);
        $builder->method('select')->willReturnSelf();
        $builder->method('leftJoin')->willReturnSelf();
        $builder->method('where')->willReturnCallback(function (string $expr) use (&$where, $builder) {
            $where[] = $expr;

            return $builder;
        });
        $builder->method('andWhere')->willReturnCallback(function (string $expr) use (&$andWhere, $builder) {
            $andWhere[] = $expr;

            return $builder;
        });
        $builder->method('setParameter')->willReturnCallback(function (string $name, mixed $value) use (&$params, $builder) {
            $params[$name] = $value;

            return $builder;
        });
        $builder->method('getQuery')->willReturn($query);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('getClassName')->willReturn(User::class);
        $repository->method('createQueryBuilder')->with('User')->willReturn($builder);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(User::class)->willReturn($repository);

        $manager = new UserManager($em, User::class, $this->createMock(PasswordHasherFactoryInterface::class));
        $manager->findEnabledByUsernameOrEmailAndScope('user@example.com');

        self::assertContains('(User.username = :identifier OR User.email = :identifier)', $where);
        self::assertContains('User.enabled = true', $andWhere);
        self::assertContains('(Scope.admin = true)', $andWhere);
        self::assertSame('user@example.com', $params['identifier'] ?? null);
    }

    public function testFindEnabledByUsernameOrEmailAndScopeAppliesScopeFilterWhenProvided(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('getOneOrNullResult')->willReturn(null);

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
        $builder->method('getQuery')->willReturn($query);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('getClassName')->willReturn(User::class);
        $repository->method('createQueryBuilder')->with('User')->willReturn($builder);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(User::class)->willReturn($repository);

        $scope = $this->createMock(ScopeInterface::class);
        $scope->method('getId')->willReturn(42);

        $manager = new UserManager($em, User::class, $this->createMock(PasswordHasherFactoryInterface::class));
        $manager->findEnabledByUsernameOrEmailAndScope('admin@example.com', $scope);

        self::assertContains('(User.scope = :scope)', $andWhere);
        self::assertSame(42, $params['scope'] ?? null);
    }
}
