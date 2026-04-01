<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Solr\Query\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOption;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\Content;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContentBlock;
use PHPUnit\Framework\TestCase;
use Solarium\Component\Facet\Field;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegratedContentBlockOptionsTest extends TestCase
{
    public function testRelevanceSearchAddsRecencyBoostFunction(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'q' => 'Banket duurder',
            'sort' => 'rel',
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertArrayHasKey('bf', $query->getParams());
        self::assertStringContainsString('recip(ms(NOW,pub_time)', (string) $query->getParams()['bf']);
    }

    public function testFreeTextSearchMatchesAllTermsByDefault(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'q' => '1 2026',
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertSame(Query::QUERY_OPERATOR_AND, $query->getQueryDefaultOperator());
    }

    public function testFacetMapOptionsAreSanitizedWithoutTypeErrors(): void
    {
        $resolver = $this->createResolver();

        $options = $resolver->resolve([
            'exclude_ids' => [' abc ', '', null, []],
            'facets' => [
                'facet_properties' => [' featured ', '', [], null],
                'facet_channels' => ' main_channel ',
                '' => ['ignored-empty-key'],
            ],
            'facets_search_selection' => [
                'facet_authors' => [' 42 ', 7, null, []],
            ],
        ]);

        self::assertSame(['abc'], $options['exclude_ids']);
        self::assertSame(['featured'], $options['facets']['facet_properties']);
        self::assertSame(['main_channel'], $options['facets']['facet_channels']);
        self::assertArrayNotHasKey('', $options['facets']);
        self::assertSame(['42', '7'], $options['facets_search_selection']['facet_authors']);
    }

    public function testFacetOperatorCanForceAndBetweenSelectedFacetValues(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'facets' => [
                'facet_company_category' => ['Machines', 'Transport'],
            ],
            'facet_operators' => [
                'facet_company_category' => 'and',
            ],
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertArrayHasKey('facet_company_category_0', $query->getFilterQueries());
        self::assertArrayHasKey('facet_company_category_1', $query->getFilterQueries());
        self::assertSame(
            'facet_company_category: ("Machines")',
            $query->getFilterQueries()['facet_company_category_0']->getQuery()
        );
        self::assertSame(
            'facet_company_category: ("Transport")',
            $query->getFilterQueries()['facet_company_category_1']->getQuery()
        );
        self::assertSame(
            ['facet_company_category'],
            $query->getFilterQueries()['facet_company_category_0']->getTags()
        );
        self::assertSame(
            ['facet_company_category'],
            $query->getFilterQueries()['facet_company_category_1']->getTags()
        );
    }

    public function testSingleSelectionModeKeepsOnlyFirstSelectedFacetValue(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'facets' => [
                'facet_company_category' => ['Machines', 'Transport'],
            ],
            'facet_selection_modes' => [
                'facet_company_category' => 'single',
            ],
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertArrayHasKey('facet_company_category', $query->getFilterQueries());
        self::assertSame(
            'facet_company_category: (("Machines"))',
            $query->getFilterQueries()['facet_company_category']->getQuery()
        );
    }

    public function testAndFacetCountsStayBoundToCurrentResultSetWithoutSelfExclusion(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'facets' => [
                'facet_company_category' => ['Machines'],
            ],
            'facet_operators' => [
                'facet_company_category' => 'and',
            ],
        ]);

        $query = new Query();
        $type->build($query, $options);

        $facet = $query->getFacetSet()->getFacet('facet_company_category');

        self::assertInstanceOf(Field::class, $facet);
        self::assertSame([], $this->getFacetExcludes($facet));
        self::assertSame(1, $facet->getMinCount());
    }

    public function testOrFacetCountsKeepSelfExclusionForBroaderFacetSuggestions(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'facets' => [
                'facet_company_category' => ['Machines'],
            ],
            'facet_operators' => [
                'facet_company_category' => 'or',
            ],
        ]);

        $query = new Query();
        $type->build($query, $options);

        $facet = $query->getFacetSet()->getFacet('facet_company_category');

        self::assertInstanceOf(Field::class, $facet);
        self::assertSame(['facet_company_category'], $this->getFacetExcludes($facet));
        self::assertSame(1, $facet->getMinCount());
    }

    public function testRelevanceRecencyBoostIsNotAddedWhenSortingByTime(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'q' => 'Banket duurder',
            'sort' => 'pub_time',
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertArrayNotHasKey('bf', $query->getParams());
    }

    public function testRelevanceRecencyBoostPreservesCustomBoostFunctions(): void
    {
        $resolver = $this->createResolver();
        $type = $this->createType();

        $options = $resolver->resolve([
            'q' => 'Banket duurder',
            'sort' => 'rel',
            'params' => [
                'bf' => 'sum(popularity,5)',
            ],
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertArrayHasKey('bf', $query->getParams());
        self::assertStringContainsString('sum(popularity,5)', (string) $query->getParams()['bf']);
        self::assertStringContainsString('recip(ms(NOW,pub_time)', (string) $query->getParams()['bf']);
    }

    public function testRelevanceSortForcesDescendingOrder(): void
    {
        $resolver = $this->createResolver();

        $options = $resolver->resolve([
            'q' => 'Banket duurder',
            'sort' => 'score',
            'order' => 'asc',
        ]);

        self::assertSame('score', $options['sort']);
        self::assertSame('desc', $options['order']);
    }

    private function createType(): IntegratedContentBlock
    {
        $sortOptions = new SortOptions([
            new SortOption('rel', 'Relevance', 'score', 'desc'),
            new SortOption('time', 'Publication date', 'pub_time', 'desc'),
        ]);

        $manager = $this->createMock(DocumentManager::class);
        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('findAll')->willReturn([]);

        $manager->method('getRepository')
            ->with(Relation::class)
            ->willReturn($repository);

        return new IntegratedContentBlock($manager, $sortOptions);
    }

    private function createResolver(): OptionsResolver
    {
        $sortOptions = new SortOptions([
            new SortOption('rel', 'Relevance', 'score', 'desc'),
            new SortOption('time', 'Publication date', 'pub_time', 'desc'),
        ]);

        $manager = $this->createMock(DocumentManager::class);
        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('findAll')->willReturn([]);

        $manager->method('getRepository')
            ->with(Relation::class)
            ->willReturn($repository);

        $resolver = new OptionsResolver();
        (new Content($sortOptions, $manager))->configureOptions($resolver);
        (new IntegratedContentBlock($manager, $sortOptions))->configureOptions($resolver);

        return $resolver;
    }

    private function getFacetExcludes(Field $facet): array
    {
        if (method_exists($facet, 'getExcludes')) {
            /** @var array $excludes */
            $excludes = $facet->getExcludes();

            return $excludes;
        }

        return $facet->getLocalParameters()->getExcludes();
    }
}
