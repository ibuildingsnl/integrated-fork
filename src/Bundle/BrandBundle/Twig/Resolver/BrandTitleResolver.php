<?php

namespace Integrated\Bundle\BrandBundle\Twig\Resolver;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Common\Solr\TitleResolverInterface;

class BrandTitleResolver implements TitleResolverInterface
{
    public function __construct(
        private readonly BrandRepository $brands,
    ) {
    }

    public function getTitle(string $id): string
    {
        return $this->brands->withId($id)?->getName() ?: $id;
    }
}
