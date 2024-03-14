<?php

namespace Integrated\Bundle\ContentBundle\Infrastructure;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;

class ChannelTypeRegistry
{
    /** @param iterable<ChannelTypeFactory> $linkTypeFactories */
    public function __construct(
        private readonly iterable $linkTypeFactories,
    ) {
    }

    public function getType(string $id): ?ChannelType
    {
        foreach ($this->linkTypeFactories as $factory) {
            if ($factory->id === $id) {
                return $factory->create();
            }
        }

        return null;
    }

    /** @return ChannelType[] */
    public function allTypes(): array
    {
        $types = [];
        foreach ($this->linkTypeFactories as $factory) {
            $types[] = $factory->create();
        }

        return $types;
    }
}
