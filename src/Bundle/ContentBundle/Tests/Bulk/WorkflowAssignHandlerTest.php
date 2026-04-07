<?php

namespace Integrated\Bundle\ContentBundle\Tests\Bulk;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowAssignHandler;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Services\AssignedStatusCacheInvalidator;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State as WorkflowState;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use PHPUnit\Framework\TestCase;

class WorkflowAssignHandlerTest extends TestCase
{
    public function testCreateStateKeepsContentVisibilityAndSyncsMetadata(): void
    {
        $content = $this->createContent(disabled: false);

        $workflow = new Definition();
        $defaultState = (new Definition\State())->setName('concept')->setPublishable(false);
        $publishedState = (new Definition\State())->setName('published')->setPublishable(true);
        $workflow->addState($defaultState);
        $workflow->addState($publishedState);
        $workflow->setDefault($defaultState);

        $assigned = $this->createUser('u1');

        $workflowStateRepository = $this->createMock(EntityRepository::class);
        $workflowStateRepository
            ->method('findOneBy')
            ->with(['content' => $content])
            ->willReturn(null);

        $workflowDefinitionRepository = $this->createMock(EntityRepository::class);
        $workflowDefinitionRepository
            ->method('find')
            ->with($workflow->getId())
            ->willReturn($workflow);

        $persistedState = null;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [WorkflowState::class, $workflowStateRepository],
                [Definition::class, $workflowDefinitionRepository],
            ]);
        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(function ($state) use (&$persistedState) {
                $persistedState = $state;

                return $state instanceof WorkflowState;
            }));
        $entityManager->expects(self::once())->method('flush');

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->method('hasType')->with('article')->willReturn(true);
        $resolver->method('getType')->with('article')->willReturn($this->createContentType($workflow->getId()));

        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('find')->with('u1')->willReturn($assigned);

        $handler = new WorkflowAssignHandler(
            $entityManager,
            $resolver,
            $userManager,
            $this->createStub(AssignedStatusCacheInvalidator::class),
            'u1'
        );
        $handler->execute($content);

        self::assertInstanceOf(WorkflowState::class, $persistedState);
        self::assertSame($publishedState, $persistedState->getState());
        self::assertSame($assigned, $persistedState->getAssigned());
        self::assertFalse((bool) $content->isDisabled());
        self::assertSame($workflow->getId(), $content->getMetadata()->get('workflow'));
        self::assertSame($publishedState->getId(), $content->getMetadata()->get('workflow_state'));
    }

    public function testSameAssigneeStillSyncsContentFromExistingWorkflowState(): void
    {
        $content = $this->createContent(disabled: false);

        $workflow = new Definition();
        $conceptState = (new Definition\State())->setName('concept')->setPublishable(false);
        $workflow->addState($conceptState);
        $workflow->setDefault($conceptState);

        $assigned = $this->createUser('u1');

        $existingState = new WorkflowState();
        $existingState->setContent($content);
        $existingState->setState($conceptState);
        $existingState->setAssigned($assigned);

        $workflowStateRepository = $this->createMock(EntityRepository::class);
        $workflowStateRepository
            ->method('findOneBy')
            ->with(['content' => $content])
            ->willReturn($existingState);

        $workflowDefinitionRepository = $this->createMock(EntityRepository::class);
        $workflowDefinitionRepository
            ->method('find')
            ->with($workflow->getId())
            ->willReturn($workflow);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [WorkflowState::class, $workflowStateRepository],
                [Definition::class, $workflowDefinitionRepository],
            ]);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->method('hasType')->with('article')->willReturn(true);
        $resolver->method('getType')->with('article')->willReturn($this->createContentType($workflow->getId()));

        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('find')->with('u1')->willReturn($assigned);

        $handler = new WorkflowAssignHandler(
            $entityManager,
            $resolver,
            $userManager,
            $this->createStub(AssignedStatusCacheInvalidator::class),
            'u1'
        );
        $handler->execute($content);

        self::assertTrue((bool) $content->isDisabled());
        self::assertSame($workflow->getId(), $content->getMetadata()->get('workflow'));
        self::assertSame($conceptState->getId(), $content->getMetadata()->get('workflow_state'));
    }

    public function testAssignmentChangeInvalidatesAssignedStatusForOldAndNewUser(): void
    {
        $content = $this->createContent(disabled: false);

        $workflow = new Definition();
        $conceptState = (new Definition\State())->setName('concept')->setPublishable(false);
        $workflow->addState($conceptState);
        $workflow->setDefault($conceptState);

        $currentAssigned = $this->createUser('old-user');
        $newAssigned = $this->createUser('new-user');

        $existingState = new WorkflowState();
        $existingState->setContent($content);
        $existingState->setState($conceptState);
        $existingState->setAssigned($currentAssigned);

        $workflowStateRepository = $this->createMock(EntityRepository::class);
        $workflowStateRepository
            ->method('findOneBy')
            ->with(['content' => $content])
            ->willReturn($existingState);

        $workflowDefinitionRepository = $this->createMock(EntityRepository::class);
        $workflowDefinitionRepository
            ->method('find')
            ->with($workflow->getId())
            ->willReturn($workflow);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [WorkflowState::class, $workflowStateRepository],
                [Definition::class, $workflowDefinitionRepository],
            ]);
        $entityManager->expects(self::once())->method('flush');

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->method('hasType')->with('article')->willReturn(true);
        $resolver->method('getType')->with('article')->willReturn($this->createContentType($workflow->getId()));

        $userManager = $this->createMock(UserManagerInterface::class);
        $userManager->method('find')->with('new-user')->willReturn($newAssigned);

        $invalidator = $this->createMock(AssignedStatusCacheInvalidator::class);
        $invalidator
            ->expects(self::once())
            ->method('invalidateUsers')
            ->with(['old-user', 'new-user']);

        $handler = new WorkflowAssignHandler(
            $entityManager,
            $resolver,
            $userManager,
            $invalidator,
            'new-user'
        );
        $handler->execute($content);
    }

    private function createContentType(string $workflowId): ContentTypeInterface
    {
        $type = $this->createMock(ContentTypeInterface::class);
        $type->method('hasOption')->with('workflow')->willReturn(true);
        $type->method('getOption')->with('workflow')->willReturn($workflowId);

        return $type;
    }

    private function createContent(bool $disabled): Content
    {
        $content = new class extends Content {
            public function __toString(): string
            {
                return '';
            }
        };
        $content->setContentType('article');
        $content->setDisabled($disabled);

        return $content;
    }

    private function createUser(string $id): User
    {
        $user = new User();

        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($user, $id);

        return $user;
    }
}
