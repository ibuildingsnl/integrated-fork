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
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\BlockBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Security\Permissions;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

    public function testUsedByFallsBackToDefaultsForArrayPaginationQueryValues(): void
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
                self::callback(fn (mixed $page): bool => \is_int($page) && 1 === $page),
                self::callback(fn (mixed $limit): bool => \is_int($limit) && 15 === $limit)
            )
            ->willReturn($pagination);

        $controller = $this->createController($documentManager, $paginator);
        $controller->setPermission('ROLE_WEBSITE_MANAGER', false);
        $controller->setPermission('ROLE_ADMIN', false);
        $controller->setPermission(Permissions::EDIT, true);

        $request = new Request(['page' => ['2'], 'limit' => ['5']]);
        $request->setRequestFormat('json');

        $response = $controller->usedBy($content, $request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame($pagination, $controller->lastParameters['pagination']);
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

    private function createController(
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        ?MetadataFactoryInterface $metadataFactory = null,
    ): TestableBlockController {
        return new TestableBlockController(
            $metadataFactory ?? $this->createStub(MetadataFactoryInterface::class),
            $documentManager,
            $paginator,
            $this->createStub(FilterQueryProvider::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(BlockRepository::class)
        );
    }

    private function createBuilderQueryResult(): object
    {
        $returnType = (new ReflectionMethod(Builder::class, 'getQuery'))->getReturnType();
        self::assertInstanceOf(ReflectionNamedType::class, $returnType);

        $type = $returnType->getName();

        try {
            return $this->createMock($type);
        } catch (\Throwable $exception) {
            if ($type !== Query::class) {
                throw $exception;
            }

            return new Query(
                $this->createMock(DocumentManager::class),
                $this->createMock(ClassMetadata::class),
                $this->createMock(Collection::class),
                ['type' => Query::TYPE_FIND, 'query' => []],
                [],
                false
            );
        }
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
