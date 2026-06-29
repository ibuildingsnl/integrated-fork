<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Integrated\Bundle\BlockBundle\Controller\BlockController;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\BlockBundle\Document\Block\ContainerBlock;
use Integrated\Bundle\BlockBundle\Document\Block\Embedded\BlockSize;
use Integrated\Bundle\BlockBundle\Document\Block\Embedded\Relation as EmbeddedRelation;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\BlockBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\BlockBundle\Security\AllowedBlockClassInstantiator;
use Integrated\Bundle\BlockBundle\Security\AllowedBlockClassProvider;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Services\WebsiteChannelResolver;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Security\Permissions;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class BlockControllerTest extends TestCase
{
    public function testUsedByAllowsNonAdminUserWithEditPermission(): void
    {
        $content = (new Article())->setId('content-id');
        $query = $this->createBuilderQueryResult();
        $pagination = $this->createMock(PaginationInterface::class);
        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder
            ->method('field')
            ->with('relations.references.$id')
            ->willReturnSelf();
        $queryBuilder
            ->method('equals')
            ->with('content-id')
            ->willReturnSelf();
        $queryBuilder
            ->method('getQuery')
            ->willReturn($query);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->with(Block::class)
            ->willReturn($queryBuilder);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects(self::once())
            ->method('paginate')
            ->with(
                $query,
                self::callback(fn (mixed $page): bool => \is_int($page) && 2 === $page),
                self::callback(fn (mixed $limit): bool => \is_int($limit) && 5 === $limit)
            )
            ->willReturn($pagination);

        $controller = $this->createController($documentManager, $paginator);
        $controller->setPermission('ROLE_WEBSITE_MANAGER', false);
        $controller->setPermission('ROLE_ADMIN', false);
        $controller->setPermission(Permissions::EDIT, true);

        $request = new Request(['page' => '2', 'limit' => '5']);
        $request->setRequestFormat('json');

        $response = $controller->usedBy($content, $request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('@IntegratedBlock/block/used_by.json.twig', $controller->lastView);
        self::assertSame($content, $controller->lastParameters['content']);
        self::assertSame($pagination, $controller->lastParameters['pagination']);
    }

    public function testUsedByRejectsArrayPaginationQueryValues(): void
    {
        $content = (new Article())->setId('content-id');
        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder
            ->method('field')
            ->with('relations.references.$id')
            ->willReturnSelf();
        $queryBuilder
            ->method('equals')
            ->with('content-id')
            ->willReturnSelf();
        $queryBuilder
            ->method('getQuery')
            ->willReturn($this->createBuilderQueryResult());

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->with(Block::class)
            ->willReturn($queryBuilder);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects(self::never())->method('paginate');

        $controller = $this->createController($documentManager, $paginator);
        $controller->setPermission('ROLE_WEBSITE_MANAGER', false);
        $controller->setPermission('ROLE_ADMIN', false);
        $controller->setPermission(Permissions::EDIT, true);

        $request = new Request(['page' => ['2'], 'limit' => ['5']]);
        $request->setRequestFormat('json');

        $this->expectException(BadRequestException::class);

        $controller->usedBy($content, $request);
    }

    public function testUsedByDeniesNonAdminUserWithoutEditPermission(): void
    {
        $content = (new Article())->setId('content-id');

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(self::never())->method('createQueryBuilder');

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects(self::never())->method('paginate');

        $controller = $this->createController($documentManager, $paginator);
        $controller->setPermission('ROLE_WEBSITE_MANAGER', false);
        $controller->setPermission('ROLE_ADMIN', false);
        $controller->setPermission(Permissions::EDIT, false);

        $this->expectException(AccessDeniedException::class);

        $controller->usedBy($content, new Request());
    }

    public function testNewRejectsBlockClassesThatAreNotRegisteredInMetadataFactory(): void
    {
        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory
            ->expects(self::once())
            ->method('getAllMetadata')
            ->willReturn([]);

        $controller = $this->createController(
            $this->createMock(DocumentManager::class),
            $this->createMock(PaginatorInterface::class),
            $metadataFactory
        );
        $controller->setPermission('ROLE_WEBSITE_MANAGER', true);
        $controller->setPermission('ROLE_ADMIN', false);

        $this->expectException(NotFoundHttpException::class);

        $controller->new(new Request(['class' => TextBlock::class]));
    }

    public function testCreateDuplicateBlockClearsRelationsAndDecouplesCollectionProperties(): void
    {
        $sourceBlock = new TextBlock();
        $sourceBlock->setId('text_block_source');
        $sourceBlock->setTitle('Source block');
        $sourceBlock->setContent('Source content');
        $sourceBlock->addRelation((new EmbeddedRelation())->setRelationId('relation-id')->setRelationType('relation-type'));
        $sourceBlock->setRequiredItems([(new Article())->setId('content-id')]);

        $controller = $this->createController(
            $this->createMock(DocumentManager::class),
            $this->createMock(PaginatorInterface::class)
        );

        $method = new \ReflectionMethod(BlockController::class, 'createDuplicateBlock');
        $method->setAccessible(true);
        $duplicate = $method->invoke($controller, $sourceBlock, '');

        self::assertInstanceOf(TextBlock::class, $duplicate);
        self::assertSame('text_block_source_copy', $duplicate->getId());
        self::assertSame('Source block (copy)', $duplicate->getTitle());
        self::assertCount(1, $sourceBlock->getRelations());
        self::assertCount(0, $duplicate->getRelations());

        $requiredItemsProperty = new \ReflectionProperty(TextBlock::class, 'requiredItems');
        $requiredItemsProperty->setAccessible(true);
        self::assertNotSame(
            $requiredItemsProperty->getValue($sourceBlock),
            $requiredItemsProperty->getValue($duplicate)
        );

        $duplicate->setRequiredItems([]);
        self::assertCount(1, $sourceBlock->getRequiredItems());
        self::assertCount(0, $duplicate->getRequiredItems());
    }

    public function testCreateDuplicateBlockKeepsContainerChildBlockReferences(): void
    {
        $childBlock = new TextBlock();
        $childBlock->setId('nieuws_home_grid');
        $childBlock->setTitle('Nieuws home grid');

        $sourceItem = (new BlockSize())->setBlock($childBlock)->setOrder(1);
        $sourceBlock = new ContainerBlock();
        $sourceBlock->setId('container_source');
        $sourceBlock->setTitle('Container source');
        $sourceBlock->setItems([$sourceItem]);

        $controller = $this->createController(
            $this->createMock(DocumentManager::class),
            $this->createMock(PaginatorInterface::class)
        );

        $method = new \ReflectionMethod(BlockController::class, 'createDuplicateBlock');
        $method->setAccessible(true);
        $duplicate = $method->invoke($controller, $sourceBlock, '');

        self::assertInstanceOf(ContainerBlock::class, $duplicate);
        self::assertSame('container_source_copy', $duplicate->getId());

        $duplicateItems = $duplicate->getItems();
        self::assertCount(1, $duplicateItems);
        self::assertNotSame($sourceItem, $duplicateItems[0]);
        self::assertSame($childBlock, $duplicateItems[0]->getBlock());
    }

    private function createController(
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        ?MetadataFactoryInterface $metadataFactory = null,
    ): TestableBlockController {
        $metadataFactory ??= $this->createStub(MetadataFactoryInterface::class);

        return new TestableBlockController(
            $metadataFactory,
            $documentManager,
            $paginator,
            $this->createStub(FilterQueryProvider::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(BlockRepository::class),
            new AllowedBlockClassInstantiator(new AllowedBlockClassProvider($metadataFactory)),
            new WebsiteChannelResolver($this->createStub(ChannelManagerInterface::class)),
        );
    }

    private function createBuilderQueryResult(): object
    {
        $returnType = (new \ReflectionMethod(Builder::class, 'getQuery'))->getReturnType();
        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);

        if ($returnType->getName() === 'Doctrine\ODM\MongoDB\Iterator\IterableResult') {
            return $this->createMock('Doctrine\ODM\MongoDB\Iterator\IterableResult');
        }

        if ($returnType->getName() === Query::class) {
            return new Query(
                $this->createMock(DocumentManager::class),
                $this->createMock(ClassMetadata::class),
                $this->createMock(Collection::class),
                ['type' => Query::TYPE_FIND, 'query' => []],
                [],
                false
            );
        }

        self::fail(\sprintf('Unsupported Builder::getQuery() return type: %s', $returnType->getName()));
    }
}

final class TestableBlockController extends BlockController
{
    public string $lastView = '';
    /** @var array<string, mixed> */
    public array $lastParameters = [];
    /** @var array<string, bool> */
    private array $permissions = [];

    public function setPermission(string $attribute, bool $granted): void
    {
        $this->permissions[$attribute] = $granted;
    }

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        if (\array_key_exists((string) $attribute, $this->permissions)) {
            return $this->permissions[(string) $attribute];
        }

        return true;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastView = $view;
        $this->lastParameters = $parameters;

        return $response ?? new Response('', Response::HTTP_OK);
    }
}
