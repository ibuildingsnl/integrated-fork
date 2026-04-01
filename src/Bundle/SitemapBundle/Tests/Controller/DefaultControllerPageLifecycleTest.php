<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator as MongoIterator;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Query;
use Integrated\Bundle\ContentBundle\Services\ContentTypeInformation;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\SitemapBundle\Controller\DefaultController;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DefaultControllerPageLifecycleTest extends TestCase
{
    public function testListPagesAppliesLifecycleFiltersToVisiblePages(): void
    {
        $capture = new LifecycleQueryCapture();
        $queryIterator = $this->createMock(MongoIterator::class);
        $query = $this->createFindQuery($queryIterator);
        [$builder, $publishExprLog, $expireExprLog, $publishNullLog, $publishLteLog, $expireNullLog, $expireGtLog] = $this->createLifecycleQueryBuilder($capture, $query);

        $manager = $this->createMock(DocumentManager::class);
        $manager->expects(self::once())
            ->method('createQueryBuilder')
            ->with(Page::class)
            ->willReturn($builder);

        $context = $this->createChannelContext('channel-1');
        $contentTypeInformation = $this->createMock(ContentTypeInformation::class);
        $controller = $this->createController($manager, $context, $contentTypeInformation);

        $response = $controller->listPages(Request::create('/sitemap-pages-1.xml'), 1);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('@IntegratedSitemap/default/list.xml.twig', $controller->lastView);
        self::assertSame(['channel.$id', 'disabled', 'path', 'path', 'hideFromSitemap'], $capture->fieldCalls);
        self::assertSame([['path', 'createdAt', 'updatedAt']], $capture->selectCalls);
        self::assertSame([['path']], $capture->sortCalls);
        self::assertSame([0], $capture->skipCalls);
        self::assertSame([50000], $capture->limitCalls);
        self::assertCount(2, $capture->addAndCalls);
        self::assertSame(2, $publishExprLog->addOrCount);
        self::assertSame(2, $expireExprLog->addOrCount);
        self::assertSame([['field', 'publishAt'], ['equals', null]], $publishNullLog->calls);
        self::assertSame('field', $publishLteLog->calls[0][0]);
        self::assertSame('publishAt', $publishLteLog->calls[0][1]);
        self::assertSame('lte', $publishLteLog->calls[1][0]);
        self::assertInstanceOf(\DateTimeInterface::class, $publishLteLog->calls[1][1]);
        self::assertSame([['field', 'expireAt'], ['equals', null]], $expireNullLog->calls);
        self::assertSame('field', $expireGtLog->calls[0][0]);
        self::assertSame('expireAt', $expireGtLog->calls[0][1]);
        self::assertSame('gt', $expireGtLog->calls[1][0]);
        self::assertInstanceOf(\DateTimeInterface::class, $expireGtLog->calls[1][1]);
    }

    public function testIndexUsesLifecycleFiltersForPageCount(): void
    {
        $capture = new LifecycleQueryCapture();
        $query = $this->createCountQuery(50001);
        [$builder, $publishExprLog, $expireExprLog, $publishNullLog, $publishLteLog, $expireNullLog, $expireGtLog] = $this->createLifecycleQueryBuilder($capture, $query);

        $manager = $this->createMock(DocumentManager::class);
        $manager->expects(self::once())
            ->method('createQueryBuilder')
            ->with(Page::class)
            ->willReturn($builder);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getId')->willReturn('channel-1');

        $context = $this->createMock(ChannelContextInterface::class);
        $context->method('getChannel')->willReturn($channel);

        $contentTypeInformation = $this->createMock(ContentTypeInformation::class);
        $contentTypeInformation
            ->expects(self::once())
            ->method('getSitemapAllowedContentTypes')
            ->with('channel-1', self::isType('array'))
            ->willReturn([]);

        $controller = $this->createController($manager, $context, $contentTypeInformation);
        $response = $controller->index(Request::create('/sitemap.xml'));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('@IntegratedSitemap/default/index.xml.twig', $controller->lastView);
        self::assertSame(2, $controller->lastParameters['pagesCount']);
        self::assertSame(['channel.$id', 'disabled', 'path', 'path', 'hideFromSitemap'], $capture->fieldCalls);
        self::assertCount(2, $capture->addAndCalls);
        self::assertSame(2, $publishExprLog->addOrCount);
        self::assertSame(2, $expireExprLog->addOrCount);
        self::assertSame([['field', 'publishAt'], ['equals', null]], $publishNullLog->calls);
        self::assertSame('field', $publishLteLog->calls[0][0]);
        self::assertSame('publishAt', $publishLteLog->calls[0][1]);
        self::assertSame('lte', $publishLteLog->calls[1][0]);
        self::assertInstanceOf(\DateTimeInterface::class, $publishLteLog->calls[1][1]);
        self::assertSame([['field', 'expireAt'], ['equals', null]], $expireNullLog->calls);
        self::assertSame('field', $expireGtLog->calls[0][0]);
        self::assertSame('expireAt', $expireGtLog->calls[0][1]);
        self::assertSame('gt', $expireGtLog->calls[1][0]);
        self::assertInstanceOf(\DateTimeInterface::class, $expireGtLog->calls[1][1]);
    }

    /**
     * @return array{0: Builder, 1: ExprLog, 2: ExprLog, 3: ExprLog, 4: ExprLog, 5: ExprLog, 6: ExprLog}
     */
    private function createLifecycleQueryBuilder(LifecycleQueryCapture $capture, Query $query): array
    {
        $publishExprLog = new ExprLog();
        $publishNullLog = new ExprLog();
        $publishLteLog = new ExprLog();
        $expireExprLog = new ExprLog();
        $expireNullLog = new ExprLog();
        $expireGtLog = new ExprLog();

        $publishExpr = $this->createExprSpy($publishExprLog);
        $publishNullExpr = $this->createExprSpy($publishNullLog);
        $publishLteExpr = $this->createExprSpy($publishLteLog);
        $expireExpr = $this->createExprSpy($expireExprLog);
        $expireNullExpr = $this->createExprSpy($expireNullLog);
        $expireGtExpr = $this->createExprSpy($expireGtLog);

        $builder = $this->createBuilderSpy(
            $capture,
            [$publishExpr, $publishNullExpr, $publishLteExpr, $expireExpr, $expireNullExpr, $expireGtExpr],
            $query,
        );

        return [
            $builder,
            $publishExprLog,
            $expireExprLog,
            $publishNullLog,
            $publishLteLog,
            $expireNullLog,
            $expireGtLog,
        ];
    }

    private function createController(
        DocumentManager $manager,
        ChannelContextInterface $context,
        ContentTypeInformation $contentTypeInformation,
    ): RenderCaptureController {
        return new RenderCaptureController($manager, $context, $contentTypeInformation);
    }

    private function createChannelContext(string $channelId): ChannelContextInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getId')->willReturn($channelId);

        $context = $this->createMock(ChannelContextInterface::class);
        $context->method('getChannel')->willReturn($channel);

        return $context;
    }

    /**
     * @param list<Expr> $exprQueue
     */
    private function createBuilderSpy(LifecycleQueryCapture $capture, array $exprQueue, Query $query): Builder
    {
        return new BuilderSpy($capture, $exprQueue, $query);
    }

    /**
     * @param MongoIterator<mixed> $iterator
     */
    private function createFindQuery(MongoIterator $iterator): Query
    {
        $collection = $this->createMock(\MongoDB\Collection::class);

        return $this->createDoctrineQuery(
            $collection,
            [
                'type' => Query::TYPE_FIND,
                'query' => [],
            ],
            $iterator
        );
    }

    private function createCountQuery(int $count): Query
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $collection
            ->expects(self::once())
            ->method('count')
            ->willReturn($count);

        return $this->createDoctrineQuery(
            $collection,
            [
                'type' => Query::TYPE_COUNT,
                'query' => [],
            ]
        );
    }

    /**
     * @param array{type: Query::TYPE_FIND, query: array<string, mixed>}|array{type: Query::TYPE_COUNT, query: array<string, mixed>} $query
     * @param MongoIterator<mixed>|null                                                                                              $iterator
     */
    private function createDoctrineQuery(\MongoDB\Collection $collection, array $query, ?MongoIterator $iterator = null): Query
    {
        $documentManager = $this->createMock(DocumentManager::class);
        $classMetadata = new \Doctrine\ODM\MongoDB\Mapping\ClassMetadata(Page::class);

        $queryObject = new Query($documentManager, $classMetadata, $collection, $query, [], false);

        if ($iterator instanceof MongoIterator) {
            $reflection = new \ReflectionProperty(Query::class, 'iterator');
            $reflection->setValue($queryObject, $iterator);
        }

        return $queryObject;
    }

    private function createExprSpy(ExprLog $capture): Expr
    {
        return new ExprSpy($capture);
    }
}

final class LifecycleQueryCapture
{
    /** @var list<string> */
    public array $fieldCalls = [];
    /** @var list<array<int, string>> */
    public array $selectCalls = [];
    /** @var list<array<int, string>> */
    public array $sortCalls = [];
    /** @var list<int> */
    public array $skipCalls = [];
    /** @var list<int> */
    public array $limitCalls = [];
    /** @var list<mixed> */
    public array $addAndCalls = [];
}

final class ExprLog
{
    public int $addOrCount = 0;
    /** @var list<array{0: string, 1: mixed}> */
    public array $calls = [];
}

final class RenderCaptureController extends DefaultController
{
    public string $lastView = '';
    /** @var array<string, mixed> */
    public array $lastParameters = [];

    /**
     * @param array<string, mixed> $parameters
     */
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $this->lastView = $view;
        $this->lastParameters = $parameters;

        return $response ?? new Response('<xml/>');
    }
}

final class BuilderSpy extends Builder
{
    /**
     * @param list<Expr> $exprQueue
     */
    public function __construct(
        private readonly LifecycleQueryCapture $capture,
        private array $exprQueue,
        private readonly Query $query,
    ) {
    }

    public function field(string $field): self
    {
        $this->capture->fieldCalls[] = $field;

        return $this;
    }

    public function equals($value): self
    {
        return $this;
    }

    public function notEqual($value): self
    {
        return $this;
    }

    public function exists(bool $bool): self
    {
        return $this;
    }

    public function select($fieldName = null): self
    {
        /** @var array<int, string> $args */
        $args = \func_get_args();
        $this->capture->selectCalls[] = $args;

        return $this;
    }

    public function sort($fieldName = null, $order = null): self
    {
        /** @var array<int, string> $args */
        $args = \func_get_args();
        $this->capture->sortCalls[] = $args;

        return $this;
    }

    public function skip(int $skip): self
    {
        $this->capture->skipCalls[] = $skip;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->capture->limitCalls[] = $limit;

        return $this;
    }

    public function count(): self
    {
        return $this;
    }

    public function addAnd($expression, ...$expressions): self
    {
        $this->capture->addAndCalls[] = $expression;

        return $this;
    }

    public function expr(): Expr
    {
        $expr = array_shift($this->exprQueue);
        if (!$expr instanceof Expr) {
            throw new \LogicException('Expression queue exhausted.');
        }

        return $expr;
    }

    public function getQuery(array $options = []): Query
    {
        return $this->query;
    }
}

final class ExprSpy extends Expr
{
    public function __construct(private readonly ExprLog $capture)
    {
    }

    public function field(string $field): self
    {
        $this->capture->calls[] = ['field', $field];

        return $this;
    }

    public function equals($value): self
    {
        $this->capture->calls[] = ['equals', $value];

        return $this;
    }

    public function lte($value): self
    {
        $this->capture->calls[] = ['lte', $value];

        return $this;
    }

    public function gt($value): self
    {
        $this->capture->calls[] = ['gt', $value];

        return $this;
    }

    public function addOr($expression, ...$expressions): self
    {
        ++$this->capture->addOrCount;

        return $this;
    }
}
