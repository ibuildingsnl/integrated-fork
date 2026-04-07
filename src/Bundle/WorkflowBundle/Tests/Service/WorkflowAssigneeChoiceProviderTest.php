<?php

namespace Integrated\Bundle\WorkflowBundle\Tests\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\WorkflowBundle\Service\WorkflowAssigneeChoiceCacheInvalidator;
use Integrated\Bundle\WorkflowBundle\Service\WorkflowAssigneeChoiceProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class WorkflowAssigneeChoiceProviderTest extends TestCase
{
    public function testGetChoicesCachesCurrentAdminScopeUsers(): void
    {
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getArrayResult'])
            ->getMock();
        $query->expects(self::once())->method('getArrayResult')->willReturn([
            ['username' => 'alice@example.test', 'id' => 10],
            ['username' => 'bob@example.test', 'id' => 12],
        ]);

        $builder = $this->createMock(QueryBuilder::class);
        $builder->method('join')->with('User.scope', 'us')->willReturnSelf();
        $builder->method('where')->with('us.admin = 1')->willReturnSelf();
        $builder->method('getQuery')->willReturn($query);

        $userManager = $this->createMock(UserManager::class);
        $userManager->expects(self::once())->method('createQueryBuilder')->willReturn($builder);

        $provider = new WorkflowAssigneeChoiceProvider($userManager, new ArrayAdapter());

        $expected = [
            'alice@example.test' => 10,
            'bob@example.test' => 12,
        ];

        self::assertSame($expected, $provider->getChoices());
        self::assertSame($expected, $provider->getChoices());
    }

    public function testInvalidationForcesChoicesToReload(): void
    {
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getArrayResult'])
            ->getMock();
        $query->expects(self::exactly(2))->method('getArrayResult')->willReturnOnConsecutiveCalls(
            [['username' => 'alice@example.test', 'id' => 10]],
            [['username' => 'bob@example.test', 'id' => 12]],
        );

        $builder = $this->createMock(QueryBuilder::class);
        $builder->method('join')->with('User.scope', 'us')->willReturnSelf();
        $builder->method('where')->with('us.admin = 1')->willReturnSelf();
        $builder->method('getQuery')->willReturn($query);

        $userManager = $this->createMock(UserManager::class);
        $userManager->expects(self::exactly(2))->method('createQueryBuilder')->willReturn($builder);

        $cache = new ArrayAdapter();
        $provider = new WorkflowAssigneeChoiceProvider($userManager, $cache);
        $invalidator = new WorkflowAssigneeChoiceCacheInvalidator($cache);

        self::assertSame(['alice@example.test' => 10], $provider->getChoices());

        $invalidator->invalidate();

        self::assertSame(['bob@example.test' => 12], $provider->getChoices());
    }
}
