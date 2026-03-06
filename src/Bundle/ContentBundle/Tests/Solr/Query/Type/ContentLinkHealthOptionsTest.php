<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Solr\Query\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOption;
use Integrated\Bundle\ContentBundle\Solr\Query\SortOptions;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\Content;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContentLinkHealthOptionsTest extends TestCase
{
    public function testLinkHealthOptionIsSanitizedAsArrayLikeFacetFilter(): void
    {
        $resolver = new OptionsResolver();
        $this->createType()->configureOptions($resolver);

        $options = $resolver->resolve([
            'link_health' => [' broken ', '', null, ['x']],
        ]);

        self::assertSame(['broken'], $options['link_health']);
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
