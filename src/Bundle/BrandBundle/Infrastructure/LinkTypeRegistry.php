<?php

namespace Integrated\Bundle\BrandBundle\Infrastructure;

use Integrated\Bundle\BrandBundle\Document\LinkType;

class LinkTypeRegistry
{
    /** @param iterable<LinkTypeFactory> $linkTypeFactories */
    public function __construct(
        private readonly iterable $linkTypeFactories,
    ) {
    }

    /** @return LinkType[] */
    public function allTypes(): array
    {
        $types = [];
        foreach ($this->linkTypeFactories as $factory) {
            $types[] = $factory->create();
        }
        return $types;
    }
}
