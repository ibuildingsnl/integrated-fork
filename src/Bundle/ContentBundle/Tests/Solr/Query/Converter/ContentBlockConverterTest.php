<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Solr\Query\Converter;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Solr\Query\Converter\ContentBlockConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ContentBlockConverterTest extends TestCase
{
    public function testConvertKeepsConfiguredFacetOperators(): void
    {
        $block = new ContentBlock();
        $block->setFacetFields(['facet_company_category', 'facet_region']);

        $request = new Request();
        $request->attributes->set('_channel', 'main');

        $settings = (new ContentBlockConverter())->convert($block, $request, [
            'facet_operators' => [
                'facet_company_category' => 'and',
                'facet_region' => 'or',
                'unknown_field' => 'and',
            ],
        ]);

        self::assertSame([
            'facet_company_category' => 'and',
            'facet_region' => 'or',
        ], $settings['facet_operators']);
    }

    public function testConvertNormalizesSingleSelectFacetFieldsToOneValue(): void
    {
        $block = new ContentBlock();
        $block->setFacetFields(['facet_company_category']);

        $request = new Request([
            'facet_company_category' => ['Hygiëne en reiniging', 'Automatisering en ict'],
        ]);
        $request->attributes->set('_channel', 'main');

        $settings = (new ContentBlockConverter())->convert($block, $request, [
            'facet_selection_modes' => [
                'facet_company_category' => 'single',
            ],
        ]);

        self::assertSame([
            'facet_company_category' => ['Hygiëne en reiniging'],
        ], $settings['facets']);
        self::assertSame([
            'facet_company_category' => 'single',
        ], $settings['facet_selection_modes']);
    }
}
