<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Integrated\Common\Content\Channel\ChannelInterface;

final class ChannelFactory implements ChannelFactoryInterface
{
    /** @var array<string, ChannelFactoryInterface> */
    private array $factories = [];

    public function __construct(iterable $factories)
    {
        /** @var ChannelFactoryInterface $factory */
        foreach ($factories as $factory) {
            foreach ($factory->supportedTypes() as $type) {
                $this->factories[$type] = $factory;
            }
        }
    }

    public function supportedTypes(): array
    {
        return array_keys($this->factories);
    }

    public function create(string $type): ChannelInterface
    {
        if (!isset($this->factories[$type])) {
            throw new \InvalidArgumentException("Unknown channel type `$type`");
        }
        return $this->factories[$type]->create($type);
    }
}
