<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Integrated\Common\Content\Channel\ChannelInterface;

final class SecondaryChannelFactory implements ChannelFactoryInterface
{
    private array $types = [];

    public function __construct(iterable $types)
    {
        foreach ($types as $type) {
            $this->types[] = $type;
        }
    }

    public function supportedTypes(): array
    {
        return $this->types;
    }

    public function create(string $type): ChannelInterface
    {
        return new SecondaryChannel($type);
    }
}
