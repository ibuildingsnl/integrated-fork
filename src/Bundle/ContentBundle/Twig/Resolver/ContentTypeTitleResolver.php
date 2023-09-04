<?php

namespace Integrated\Bundle\ContentBundle\Twig\Resolver;

use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Solr\TitleResolverInterface;

class ContentTypeTitleResolver implements TitleResolverInterface
{
    public function __construct(
        private readonly ResolverInterface $resolver,
    ) {
    }

    public function getTitle(string $id): string
    {
        if ($this->resolver->hasType($id)) {
            return $this->resolver->getType($id)->getName();
        }

        return $id;
    }
}
