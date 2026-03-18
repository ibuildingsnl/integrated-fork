<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

final class BlockCopyInstruction
{
    public const OPERATION_REUSE = '';
    public const OPERATION_CLONE = 'clone';

    public function __construct(
        private readonly string $sourceBlockId,
        private readonly string $operation,
        private readonly ?string $targetBlockId,
    ) {
    }

    public function getSourceBlockId(): string
    {
        return $this->sourceBlockId;
    }

    public function isClone(): bool
    {
        return $this->operation === self::OPERATION_CLONE;
    }

    public function getTargetBlockId(): ?string
    {
        return $this->targetBlockId;
    }
}
