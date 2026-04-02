<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Security;

use Integrated\Bundle\BlockBundle\Document\Block\Block;

final class AllowedBlockClassInstantiator
{
    public function __construct(
        private readonly AllowedBlockClassProvider $allowedBlockClassProvider,
    ) {
    }

    /**
     * @throws InvalidBlockClassException
     */
    public function instantiate(mixed $class, mixed ...$arguments): Block
    {
        if (!\is_string($class) || !$this->allowedBlockClassProvider->isAllowed($class)) {
            throw new InvalidBlockClassException(\sprintf('Invalid block "%s"', (string) $class));
        }

        try {
            $block = new $class(...$arguments);
        } catch (\Throwable $exception) {
            throw new InvalidBlockClassException(\sprintf('Invalid block "%s"', $class), previous: $exception);
        }

        if (!$block instanceof Block) {
            throw new InvalidBlockClassException(\sprintf('Invalid block "%s"', $class));
        }

        return $block;
    }
}
