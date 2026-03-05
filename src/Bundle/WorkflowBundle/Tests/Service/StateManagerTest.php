<?php

namespace Integrated\Bundle\WorkflowBundle\Tests\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State as DefinitionState;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State as WorkflowState;
use Integrated\Bundle\WorkflowBundle\Service\StateManager;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;

class StateManagerTest extends TestCase
{
    public function testEnsureWorkflowStateBatchesFlushCallsAcrossMultipleItems(): void
    {
        $workflow = new Definition();
        $defaultState = (new DefinitionState())
            ->setName('Published')
            ->setPublishable(true);
        $workflow->setDefault($defaultState);

        $contentType = (new ContentType())
            ->setId('article')
            ->setOption('workflow', $workflow->getId());

        $contentA = (new Article())
            ->setId('content-a')
            ->setContentType('article');
        $contentB = (new Article())
            ->setId('content-b')
            ->setContentType('article');

        $items = [
            ['_id' => 'content-a', 'class' => Article::class],
            ['_id' => 'content-b', 'class' => Article::class],
        ];

        $query = $this->createBuilderQueryResult($items);

        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('field')->willReturnSelf();
        $queryBuilder->method('equals')->willReturnSelf();
        $queryBuilder->method('hydrate')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $contentTypeRepository = $this->createMock(ObjectRepository::class);
        $contentTypeRepository->expects(self::once())
            ->method('find')
            ->with('article')
            ->willReturn($contentType);

        $contentRepository = $this->createMock(ObjectRepository::class);
        $contentRepository->expects(self::exactly(2))
            ->method('find')
            ->willReturnMap([
                ['content-a', $contentA],
                ['content-b', $contentB],
            ]);

        $stateRepository = $this->createMock(EntityRepository::class);
        $stateRepository->expects(self::exactly(2))
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(null, null);

        $workflowRepository = $this->createMock(EntityRepository::class);
        $workflowRepository->expects(self::once())
            ->method('find')
            ->with($workflow->getId())
            ->willReturn($workflow);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturnMap([
            [Definition::class, $workflowRepository],
            [WorkflowState::class, $stateRepository],
        ]);
        $entityManager->expects(self::exactly(2))->method('persist')->with(self::isInstanceOf(WorkflowState::class));
        $entityManager->expects(self::once())->method('flush');

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->method('getRepository')->willReturnMap([
            [ContentType::class, $contentTypeRepository],
            [Content::class, $contentRepository],
        ]);
        $documentManager->expects(self::once())->method('createQueryBuilder')->with(Content::class)->willReturn($queryBuilder);
        $documentManager->expects(self::exactly(2))->method('persist')->with(self::isInstanceOf(Content::class));
        $documentManager->expects(self::once())->method('flush');

        $manager = new StateManager($entityManager, $documentManager);
        $manager->ensureWorkflowState('article');
    }

    /**
     * @param array<int, array{_id: string, class: class-string<Content>}> $items
     */
    private function createBuilderQueryResult(array $items): object
    {
        $returnType = (new \ReflectionMethod(Builder::class, 'getQuery'))->getReturnType();
        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);

        $type = $returnType->getName();

        try {
            $query = $this->createMock($type);
            $query->expects(self::once())->method('execute')->willReturn($items);

            return $query;
        } catch (\Throwable $exception) {
            if ($type !== Query::class) {
                throw $exception;
            }

            $collection = $this->createMock(Collection::class);
            $collection
                ->expects(self::once())
                ->method('find')
                ->with([], self::isType('array'))
                ->willReturn(new \ArrayIterator($items));

            return new Query(
                $this->createMock(DocumentManager::class),
                $this->createMock(ClassMetadata::class),
                $collection,
                ['type' => Query::TYPE_FIND, 'query' => []],
                [],
                false
            );
        }
    }
}
