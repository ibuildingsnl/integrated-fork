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
use Integrated\Bundle\ContentBundle\Solr\Query\SortOption;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\Content;
use PHPUnit\Framework\TestCase;
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
        ]);

        self::assertSame(['bioprocessing_news'], $options['brands']);
        self::assertSame(['channel_a'], $options['channels']);
        self::assertSame(['featured'], $options['properties']);
        self::assertSame([], $options['authors']);
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

    private function createType(): Content
    {
        $sortOptions = new SortOptions([
            new SortOption('rel', 'Relevance', 'score', 'desc'),
            new SortOption('time', 'Publication date', 'pub_time', 'desc'),
        ]);

        return new Content($sortOptions, $this->createMock(DocumentManager::class));
    }
}
