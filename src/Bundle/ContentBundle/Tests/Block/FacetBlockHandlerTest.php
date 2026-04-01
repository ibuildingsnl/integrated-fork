<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Block;

use Integrated\Bundle\ContentBundle\Block\ContentBlockHandler;
use Integrated\Bundle\ContentBundle\Block\FacetBlockHandler;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\Embedded\FacetField;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Common\Block\BlockHandlerRegistryInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PHPUnit\Framework\TestCase;
use Solarium\Component\Result\Facet\FacetResultInterface;
use Solarium\Component\Result\FacetSet;
use Solarium\QueryType\Select\Result\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class FacetBlockHandlerTest extends TestCase
{
    public function testExecutePassesFacetOperatorForConfiguredFields(): void
    {
        $contentBlock = new ContentBlock();
        $facetBlock = new FacetBlock();
        $facetBlock->setBlock($contentBlock);
        $facetBlock->setOperator('and');
        $facetBlock->setSelectionMode('single');

        $field = new FacetField();
        $field->setName('Company category');
        $field->setField('facet_company_category');
        $facetBlock->setFields([$field]);

        $pagination = $this->createMock(PaginationInterface::class);
        $result = $this->createMock(Result::class);
        $facetSet = $this->createMock(FacetSet::class);
        $facetResult = $this->createMock(FacetResultInterface::class);

        $pagination->method('getCustomParameter')
            ->with('result')
            ->willReturn($result);

        $result->method('getFacetSet')->willReturn($facetSet);
        $facetSet->method('getFacet')->with('facet_company_category')->willReturn($facetResult);

        $contentHandler = $this->getMockBuilder(ContentBlockHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPagination'])
            ->getMock();

        $contentHandler->expects(self::once())
            ->method('getPagination')
            ->with(
                $contentBlock,
                self::isInstanceOf(Request::class),
                self::callback(static function (array $options): bool {
                    return false === ($options['exclude'] ?? true)
                        && ['facet_company_category' => 'and'] === ($options['facet_operators'] ?? null)
                        && ['facet_company_category' => 'single'] === ($options['facet_selection_modes'] ?? null);
                })
            )
            ->willReturn($pagination);

        $registry = $this->createMock(BlockHandlerRegistryInterface::class);
        $registry->method('getHandler')->with('content')->willReturn($contentHandler);

        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $handler = new TestableFacetBlockHandler($registry, $requestStack);

        $handler->execute($facetBlock, []);
        $parameters = $handler->getCapturedParameters();

        self::assertArrayHasKey('facets', $parameters);
    }
}

final class TestableFacetBlockHandler extends FacetBlockHandler
{
    /**
     * @var array<string, mixed>
     */
    private array $capturedParameters = [];

    /**
     * @param array<string, mixed> $parameters
     */
    public function render(array $parameters = [])
    {
        $this->capturedParameters = $parameters;

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCapturedParameters(): array
    {
        return $this->capturedParameters;
    }
}
