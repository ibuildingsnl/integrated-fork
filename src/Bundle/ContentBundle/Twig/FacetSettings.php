<?php

namespace Integrated\Bundle\ContentBundle\Twig;

use Integrated\Common\Solr\TitleResolverInterface;

class FacetSettings
{
    public function __construct(
        public readonly string $tag,
        public readonly bool $show,
        public readonly TitleResolverInterface $titleResolver,
    ) {
    }

    public function titleFor(string $id): string
    {
        return $this->titleResolver->getTitle($id);
    }
}
