<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Block;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Block\RelatedContentBlockHandler;
use Integrated\Bundle\ContentBundle\Document\Block\RelatedContentBlock;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RelatedContentBlockHandlerTest extends TestCase
{
    public function testGetPaginationNormalizesCappedSinglePagePagination(): void
    {
        $block = new RelatedContentBlock();
        $block->setId('related-content');
        $block->setItemsPerPage(3);
        $block->setMaxItems(3);

        $request = new Request([
            'related-content-page' => 2,
        ]);

        self::assertSame(3, $block->getItemsPerPage());
        self::assertSame(3, $block->getMaxItems());
        self::assertSame(2, $request->query->get('related-content-page'));

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects(self::once())
            ->method('paginate')
            ->with(
                self::callback(static function (mixed $target): bool {
                    return \is_array($target) && 3 === \count($target);
                }),
                self::callback(static fn (mixed $page): bool => 1 === $page),
                self::callback(static fn (mixed $limit): bool => 3 === $limit),
                self::callback(static function (array $options): bool {
                    return ($options['pageParameterName'] ?? null) === 'related-content-page'
                        && ($options['maxItems'] ?? null) === 3;
                })
            )
            ->willReturnCallback(static function (): SlidingPagination {
                $pagination = new SlidingPagination();
                $pagination->setCurrentPageNumber(2);
                $pagination->setItemNumberPerPage(3);
                $pagination->setTotalItemCount(10);
                $pagination->setPaginatorOptions([]);
                $pagination->setCustomParameters([]);
                $pagination->setItems(['first', 'second', 'third']);

                return $pagination;
            });

        $handler = new TestableRelatedContentBlockHandler(
            $paginator,
            $this->createMock(RequestStack::class),
            $this->createMock(DocumentManager::class),
            $this->createMock(ContentRepository::class),
            ['first', 'second', 'third', 'fourth']
        );

        $pagination = $handler->getPagination($block, $request);

        self::assertInstanceOf(PaginationInterface::class, $pagination);
        self::assertCount(3, $pagination);
        self::assertSame(1, $pagination->getCurrentPageNumber());
        self::assertSame(3, $pagination->getTotalItemCount());
        self::assertSame(1, $pagination->getPaginationData()['pageCount']);
        self::assertArrayNotHasKey('next', $pagination->getPaginationData());
    }

    public function testGetPaginationKeepsMultiPagePaginationWhenCapSpansMultiplePages(): void
    {
        $block = new RelatedContentBlock();
        $block->setId('related-content');
        $block->setItemsPerPage(3);
        $block->setMaxItems(6);

        $request = new Request([
            'related-content-page' => 2,
        ]);

        $paginator = $this->createMock(PaginatorInterface::class);
        $queryBuilder = $this->createMock(\Doctrine\ODM\MongoDB\Query\Builder::class);
        $paginator
            ->expects(self::once())
            ->method('paginate')
            ->with(
                self::isType('object'),
                self::callback(static fn (mixed $page): bool => 2 === $page),
                self::callback(static fn (mixed $limit): bool => 3 === $limit),
                self::callback(static function (array $options): bool {
                    return ($options['pageParameterName'] ?? null) === 'related-content-page'
                        && ($options['maxItems'] ?? null) === 6;
                })
            )
            ->willReturnCallback(static function (): SlidingPagination {
                $pagination = new SlidingPagination();
                $pagination->setCurrentPageNumber(2);
                $pagination->setItemNumberPerPage(3);
                $pagination->setTotalItemCount(10);
                $pagination->setPaginatorOptions([]);
                $pagination->setCustomParameters([]);
                $pagination->setItems(['fourth', 'fifth', 'sixth']);

                return $pagination;
            });

        $handler = new TestableRelatedContentBlockHandler(
            $paginator,
            $this->createMock(RequestStack::class),
            $this->createMock(DocumentManager::class),
            $this->createMock(ContentRepository::class),
            $queryBuilder
        );

        $pagination = $handler->getPagination($block, $request);

        self::assertInstanceOf(SlidingPagination::class, $pagination);
        self::assertCount(3, $pagination);
        self::assertSame(2, $pagination->getCurrentPageNumber());
        self::assertSame(6, $pagination->getTotalItemCount());
        self::assertSame(2, $pagination->getPaginationData()['pageCount']);
        self::assertArrayNotHasKey('next', $pagination->getPaginationData());
        self::assertSame(1, $pagination->getPaginationData()['previous']);
    }
}

final class TestableRelatedContentBlockHandler extends RelatedContentBlockHandler
{
    public function __construct(
        PaginatorInterface $paginator,
        RequestStack $requestStack,
        DocumentManager $dm,
        ContentRepository $contentRepository,
        private readonly mixed $queryBuilder,
    ) {
        parent::__construct($paginator, $requestStack, $dm, $contentRepository);
    }

    /**
     * @return mixed
     */
    protected function getQuery(RelatedContentBlock $block, mixed $document)
    {
        return $this->queryBuilder;
    }
}
