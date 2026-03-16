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
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOption;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\Content;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentOptionsTest extends TestCase
{
    public function testArrayLikeOptionsAreSanitized(): void
    {
        $resolver = new OptionsResolver();
        $this->createType()->configureOptions($resolver);

        $options = $resolver->resolve([
            'brands' => ' bioprocessing_news ',
            'channels' => [' channel_a ', '', null, []],
            'properties' => [' featured ', ' ', 1, ['x']],
            'authors' => '',
            'exclude_contenttypes' => [' image ', '', null, []],
        ]);

        self::assertSame(['bioprocessing_news'], $options['brands']);
        self::assertSame(['channel_a'], $options['channels']);
        self::assertSame(['featured'], $options['properties']);
        self::assertSame([], $options['authors']);
        self::assertSame(['image'], $options['exclude_contenttypes']);
    }

    public function testSortFallsBackToConfiguredDefaultWhenUnknownSortIsUsed(): void
    {
        $resolver = new OptionsResolver();
        $this->createType()->configureOptions($resolver);

        $options = $resolver->resolve([
            'q' => '',
            'sort' => 'unknown_sort',
            'order' => '',
        ]);

        self::assertSame('pub_time', $options['sort']);
        self::assertSame('desc', $options['order']);
    }

    public function testCustomSortAndOrderAreAccepted(): void
    {
        $resolver = new OptionsResolver();
        $this->createType()->configureOptions($resolver);

        $options = $resolver->resolve([
            'q' => '',
            'sort' => 'custom:publication_start_vismagazine_index_date',
            'order' => 'asc',
        ]);

        self::assertSame('publication_start_vismagazine_index_date', $options['sort']);
        self::assertSame('asc', $options['order']);
    }

    public function testRelevanceSortForcesDescendingOrder(): void
    {
        $resolver = new OptionsResolver();
        $this->createType()->configureOptions($resolver);

        $options = $resolver->resolve([
            'q' => 'Banket duurder',
            'sort' => 'score',
            'order' => 'asc',
        ]);

        self::assertSame('score', $options['sort']);
        self::assertSame('desc', $options['order']);
    }

    public function testContentChoiceSearchUsesBrandsAndMatchesAllTerms(): void
    {
        $resolver = new OptionsResolver();
        $type = $this->createType();
        $type->configureOptions($resolver);

        $options = $resolver->resolve([
            'q' => 'Bakkerij Nollen',
            'search_context' => 'filterable_content_choice',
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertSame('title content', $query->getEDisMax()->getQueryFields());
        self::assertSame('100%', $query->getEDisMax()->getMinimumMatch());
        self::assertSame(Query::QUERY_OPERATOR_AND, $query->getQueryDefaultOperator());
        self::assertSame('*:*', $query->getQuery());
        self::assertArrayHasKey('content_choice_brands', $query->getFilterQueries());
        self::assertSame('facet_brands: (("bakkerij_nollen"))', $query->getFilterQueries()['content_choice_brands']->getQuery());
    }

    public function testContentChoiceSearchSplitsBrandAndContentTerms(): void
    {
        $resolver = new OptionsResolver();
        $type = $this->createType();
        $type->configureOptions($resolver);

        $options = $resolver->resolve([
            'q' => 'Vismagazine machines',
            'search_context' => 'filterable_content_choice',
        ]);

        $query = new Query();
        $type->build($query, $options);

        self::assertSame('machines*', $query->getQuery());
        self::assertArrayHasKey('content_choice_brands', $query->getFilterQueries());
        self::assertSame('facet_brands: (("vismagazine"))', $query->getFilterQueries()['content_choice_brands']->getQuery());
    }

    private function createType(): Content
    {
        $sortOptions = new SortOptions([
            new SortOption('rel', 'Relevance', 'score', 'desc'),
            new SortOption('time', 'Publication date', 'pub_time', 'desc'),
        ]);

        $manager = $this->createMock(DocumentManager::class);
        $manager->method('getRepository')->with(Brand::class)->willReturn($this->createBrandRepository());

        return new Content($sortOptions, $manager);
    }

    private function createBrandRepository(): ObjectRepository
    {
        return new class() implements ObjectRepository {
            private ?array $brands = null;

            private function brands(): array
            {
                return $this->brands ??= [
                    $this->createBrand('bakkerij_nollen', 'Bakkerij Nollen'),
                    $this->createBrand('vismagazine', 'Vismagazine'),
                ];
            }

            public function find(mixed $id): ?object
            {
                foreach ($this->brands() as $brand) {
                    if ($brand->getId() === $id) {
                        return $brand;
                    }
                }

                return null;
            }

            public function add(Brand $brand): void
            {
            }

            public function remove(Brand $brand): void
            {
            }

            public function findAll(): array
            {
                return $this->brands();
            }

            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                return [];
            }

            public function findOneBy(array $criteria): ?object
            {
                return null;
            }

            public function getClassName(): string
            {
                return Brand::class;
            }

            private function createBrand(string $id, string $name): Brand
            {
                $profile = new BrandProfile();
                $profile->name = $name;

                $brand = new Brand($profile);
                $brand->setId($id);

                return $brand;
            }
        };
    }
}
