<?php

namespace Integrated\Bundle\UserBundle\Tests\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\UserBundle\Doctrine\ScopeManager;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\WorkflowBundle\Service\WorkflowAssigneeChoiceCacheInvalidator;
use PHPUnit\Framework\TestCase;

class ScopeManagerTest extends TestCase
{
    public function testPersistInvalidatesWorkflowAssigneeChoices(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('getClassName')->willReturn(Scope::class);

        $om = $this->createMock(ObjectManager::class);
        $om->method('getRepository')->with(Scope::class)->willReturn($repository);
        $om->expects(self::once())->method('persist');
        $om->expects(self::once())->method('flush');

        $invalidator = $this->createMock(WorkflowAssigneeChoiceCacheInvalidator::class);
        $invalidator->expects(self::once())->method('invalidate');

        $manager = new ScopeManager($om, Scope::class, $invalidator);
        $manager->persist(new Scope());
    }

    public function testRemoveInvalidatesWorkflowAssigneeChoices(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('getClassName')->willReturn(Scope::class);

        $om = $this->createMock(ObjectManager::class);
        $om->method('getRepository')->with(Scope::class)->willReturn($repository);
        $om->expects(self::once())->method('remove');
        $om->expects(self::once())->method('flush');

        $invalidator = $this->createMock(WorkflowAssigneeChoiceCacheInvalidator::class);
        $invalidator->expects(self::once())->method('invalidate');

        $manager = new ScopeManager($om, Scope::class, $invalidator);
        $manager->remove(new Scope());
    }
}
