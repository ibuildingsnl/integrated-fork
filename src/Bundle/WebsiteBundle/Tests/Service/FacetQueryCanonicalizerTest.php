<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Service;

use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\Embedded\FacetField;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\WebsiteBundle\Service\FacetQueryCanonicalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class FacetQueryCanonicalizerTest extends TestCase
{
    public function testSingleSelectFacetFieldsAreReducedToOneQueryValue(): void
    {
        $facetBlock = new FacetBlock('facet-block');
        $facetBlock->setBlock(new ContentBlock());
        $facetBlock->setSelectionMode(FacetBlock::SELECTION_MODE_SINGLE);

        $field = new FacetField();
        $field->setField('facet_company_category');
        $field->setName('Company category');
        $facetBlock->setFields([$field]);

        $page = new Page();
        $page->setPath('/bedrijvengids');
        $page->setBlockIds(['facet-block']);

        $repository = $this->createMock(BlockRepository::class);
        $repository->expects(self::once())
            ->method('find')
            ->with('facet-block')
            ->willReturn($facetBlock);

        $request = Request::create('https://example.test/bedrijvengids?facet_company_category%5B0%5D=Automatisering&facet_company_category%5B1%5D=Dienstverlening&sort=title');

        $normalizedPath = (new FacetQueryCanonicalizer($repository))->getNormalizedPath($page, $request);

        self::assertSame('/bedrijvengids?facet_company_category%5B0%5D=Automatisering&sort=title', $normalizedPath);
    }

    public function testMultiSelectFacetFieldsDoNotTriggerNormalization(): void
    {
        $facetBlock = new FacetBlock('facet-block');
        $facetBlock->setBlock(new ContentBlock());
        $facetBlock->setSelectionMode(FacetBlock::SELECTION_MODE_MULTI);

        $field = new FacetField();
        $field->setField('facet_company_category');
        $field->setName('Company category');
        $facetBlock->setFields([$field]);

        $page = new Page();
        $page->setPath('/bedrijvengids');
        $page->setBlockIds(['facet-block']);

        $repository = $this->createMock(BlockRepository::class);
        $repository->expects(self::once())
            ->method('find')
            ->with('facet-block')
            ->willReturn($facetBlock);

        $request = Request::create('https://example.test/bedrijvengids?facet_company_category%5B0%5D=Automatisering&facet_company_category%5B1%5D=Dienstverlening');

        $normalizedPath = (new FacetQueryCanonicalizer($repository))->getNormalizedPath($page, $request);

        self::assertNull($normalizedPath);
    }
}
