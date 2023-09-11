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

    public function getType(string $id): ?LinkType
    {
        foreach ($this->linkTypeFactories as $factory) {
            if ($factory->id === $id) {
                return $factory->create();
            }
        }

        return null;
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
