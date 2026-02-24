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
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegratedContentBlockOptionsTest extends TestCase
{
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
}
