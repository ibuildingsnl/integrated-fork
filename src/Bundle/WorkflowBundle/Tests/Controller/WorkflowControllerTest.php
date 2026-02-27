<?php

namespace Integrated\Bundle\WorkflowBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Controller\WorkflowController;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\Permission;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;
use Integrated\Common\Security\PermissionInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WorkflowControllerTest extends TestCase
{
    public function testChangeStateReturnsEmptyPayloadWhenWorkflowDoesNotExist(): void
    {
        $workflowRepository = $this->createMock(EntityRepository::class);
        $workflowRepository->expects(self::once())
            ->method('find')
            ->with('missing-workflow')
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects(self::once())
            ->method('getRepository')
            ->with(Definition::class)
            ->willReturn($workflowRepository);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::never())->method('getRepository');

        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->expects(self::never())->method('getClassName');

        $controller = $this->createController($entityManager, $documentManager, $userManager);

        $response = $controller->changeState(new Request([
            'workflow' => 'missing-workflow',
            'state' => '',
        ]));

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(['users' => [], 'fields' => []], json_decode((string) $response->getContent(), true));
    }

    public function testChangeStateKeepsAdminScopeFilterWhenPermissionFilterIsApplied(): void
    {
        $state = new State();
        $permission = new Permission();
        $permission->setGroup('group-write');
        $permission->setMask(PermissionInterface::WRITE);
        $state->addPermission($permission);

        $stateRepository = $this->createMock(EntityRepository::class);
        $stateRepository->expects(self::once())
            ->method('find')
            ->with('state-1')
            ->willReturn($state);

        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('getResult')->willReturn([]);

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $joins = [];
        $where = [];
        $andWhere = [];
        $parameters = [];

        $queryBuilder->method('join')->willReturnCallback(function ($field, $alias) use (&$joins, $queryBuilder) {
            $joins[] = [$field, $alias];

            return $queryBuilder;
        });

        $queryBuilder->method('where')->willReturnCallback(function ($condition) use (&$where, $queryBuilder) {
            $where[] = $condition;

            return $queryBuilder;
        });

        $queryBuilder->method('andWhere')->willReturnCallback(function ($condition) use (&$andWhere, $queryBuilder) {
            $andWhere[] = $condition;

            return $queryBuilder;
        });

        $queryBuilder->method('setParameter')->willReturnCallback(function ($key, $value) use (&$parameters, $queryBuilder) {
            $parameters[] = [$key, $value];

            return $queryBuilder;
        });

        $queryBuilder->method('getQuery')->willReturn($query);

        $userRepository = $this->createMock(EntityRepository::class);
        $userRepository->expects(self::once())
            ->method('createQueryBuilder')
            ->with('u')
            ->willReturn($queryBuilder);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturnMap([
            [Definition\State::class, $stateRepository],
            [User::class, $userRepository],
        ]);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::never())->method('getRepository');

        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->expects(self::once())->method('getClassName')->willReturn(User::class);

        $currentUser = $this->createMock(User::class);
        $currentUser->expects(self::once())->method('getGroups')->willReturn([new Group('other-group')]);

        $controller = $this->createController($entityManager, $documentManager, $userManager, $currentUser);

        $response = $controller->changeState(new Request([
            'state' => 'state-1',
            'workflow' => 'ignored',
            'contentType' => 'ignored',
        ]));

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertContains(['u.scope', 'us'], $joins);
        self::assertContains(['u.groups', 'ug'], $joins);
        self::assertSame(['us.admin = 1'], $where);
        self::assertSame(['ug.id IN (:groups)'], $andWhere);
        self::assertSame([['groups', ['group-write']]], $parameters);
    }

    /**
     * @return TestWorkflowController
     */
    private function createController(EntityManager $entityManager, DocumentManager $documentManager, UserManagerInterface $userManager, ?User $currentUser = null)
    {
        return new TestWorkflowController(
            $entityManager,
            $documentManager,
            $this->createStub(PaginatorInterface::class),
            $userManager,
            $currentUser
        );
    }
}

class TestWorkflowController extends WorkflowController
{
    /**
     * @var User|null
     */
    private $currentUser;

    public function __construct(EntityManager $entityManager, DocumentManager $documentManager, PaginatorInterface $paginator, UserManagerInterface $userManager, ?User $currentUser)
    {
        parent::__construct($entityManager, $documentManager, $paginator, $userManager);
        $this->currentUser = $currentUser;
    }

    protected function getUser(): ?User
    {
        return $this->currentUser;
    }
}
