<?php

declare(strict_types=1);

namespace Integrated\Bundle\SitemapBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\IterableResult;
use Doctrine\ODM\MongoDB\Iterator\Iterator as MongoIterator;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
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
        $capture = (object) [
            'fieldCalls' => [],
            'selectCalls' => [],
            'sortCalls' => [],
            'skipCalls' => [],
            'limitCalls' => [],
            'addAndCalls' => [],
        ];
        $query = $this->createMock(IterableResult::class);
        $queryIterator = $this->createMock(MongoIterator::class);
        $query->expects(self::once())->method('getIterator')->willReturn($queryIterator);
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
        $capture = (object) [
            'fieldCalls' => [],
            'selectCalls' => [],
            'sortCalls' => [],
            'skipCalls' => [],
            'limitCalls' => [],
            'addAndCalls' => [],
        ];
        $query = $this->createMock(IterableResult::class);
        $query->expects(self::once())->method('execute')->willReturn(50001);
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
     * @return array{0: Builder, 1: object, 2: object, 3: object, 4: object, 5: object, 6: object}
     */
    private function createLifecycleQueryBuilder(object $capture, IterableResult $query): array
    {
        $publishExprLog = (object) ['addOrCount' => 0];
        $publishNullLog = (object) ['calls' => []];
        $publishLteLog = (object) ['calls' => []];
        $expireExprLog = (object) ['addOrCount' => 0];
        $expireNullLog = (object) ['calls' => []];
        $expireGtLog = (object) ['calls' => []];

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
    ): object {
        return new class($manager, $context, $contentTypeInformation) extends DefaultController {
            public string $lastView = '';
            public array $lastParameters = [];

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->lastView = $view;
                $this->lastParameters = $parameters;

                return $response ?? new Response('<xml/>');
            }
        };
    }

    private function createChannelContext(string $channelId): ChannelContextInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getId')->willReturn($channelId);

        $context = $this->createMock(ChannelContextInterface::class);
        $context->method('getChannel')->willReturn($channel);

        return $context;
    }

    private function createBuilderSpy(object $capture, array $exprQueue, IterableResult $query): Builder
    {
        return new class($capture, $exprQueue, $query) extends Builder {
            /**
             * @param list<Expr> $exprQueue
             */
            public function __construct(private object $capture, private array $exprQueue, private IterableResult $query)
            {
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
                $this->capture->selectCalls[] = \func_get_args();

                return $this;
            }

            public function sort($fieldName = null, $order = null): self
            {
                $this->capture->sortCalls[] = \func_get_args();

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
                return array_shift($this->exprQueue);
            }

            public function getQuery(array $options = []): IterableResult
            {
                return $this->query;
            }
        };
    }

    private function createExprSpy(object $capture): Expr
    {
        return new class($capture) extends Expr {
            public function __construct(private object $capture)
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
        };
    }
}
