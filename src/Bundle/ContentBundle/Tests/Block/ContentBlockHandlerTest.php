<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Block;

use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\ContentBundle\Block\ContentBlockHandler;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\Embedded\FacetField;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Bundle\ContentBundle\Solr\Query\Provider\IntegratedContentBlock as IntegratedContentBlockProvider;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ContentBlockHandlerTest extends TestCase
{
    public function testGetPaginationInjectsFacetBlockOperatorAndSelectionModeFromCurrentPage(): void
    {
        $contentBlock = new ContentBlock('content_block');
        $contentBlock->setItemsPerPage(10);

        $facetBlock = new FacetBlock('facet_block');
        $facetBlock->setBlock($contentBlock);
        $facetBlock->setOperator('and');
        $facetBlock->setSelectionMode('multi');

        $field = new FacetField();
        $field->setField('facet_company_category');
        $field->setName('Company category');
        $facetBlock->setFields([$field]);

        $page = new Page();
        $page->setBlockIds([$facetBlock->getId()]);

        $request = new Request();
        $request->attributes->set('_integrated_page_document', $page);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $pagination = $this->createMock(PaginationInterface::class);

        $provider = $this->createMock(IntegratedContentBlockProvider::class);
        $provider->expects(self::once())
            ->method('get')
            ->with(
                $contentBlock,
                self::isInstanceOf(Request::class),
                self::callback(static function (array $options): bool {
                    return ['facet_company_category' => 'and'] === ($options['facet_operators'] ?? null)
                        && ['facet_company_category' => 'multi'] === ($options['facet_selection_modes'] ?? null);
                })
            )
            ->willReturn($pagination);

        $blockRepository = $this->createMock(BlockRepository::class);
        $blockRepository->expects(self::once())
            ->method('find')
            ->with('facet_block')
            ->willReturn($facetBlock);

        $handler = new ContentBlockHandler($provider, $requestStack, $blockRepository);

        self::assertSame($pagination, $handler->getPagination($contentBlock, $request));
    }
}
