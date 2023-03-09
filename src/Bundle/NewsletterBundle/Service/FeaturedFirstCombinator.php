<?php

namespace Integrated\Bundle\NewsletterBundle\Service;

use Integrated\Bundle\NewsletterBundle\Document\CombinedContent;
use Stratadox\Sorting\Contracts\Sorter;
use Stratadox\Sorting\Sort;

final class FeaturedFirstCombinator implements CombinatorInterface
{
    public function __construct(
        private readonly CombinatorInterface $combinator,
        private readonly Sorter $sorter,
    ) {
    }

    public function combine(array $types, array $channels = []): CombinedContent
    {
        return CombinedContent::fromItems(...$this->sorter->sort(
            $this->combinator->combine($types, $channels)->getContent(),
            Sort::descendingBy('isFeatured'),
        ));
    }
}
