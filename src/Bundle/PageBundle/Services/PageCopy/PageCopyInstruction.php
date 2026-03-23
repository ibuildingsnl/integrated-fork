<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

final class PageCopyInstruction
{
    public const ACTION_CREATE = 'create';
    public const ACTION_OVERWRITE = 'overwrite';

    /**
     * @param array<string, BlockCopyInstruction> $blockInstructions
     */
    public function __construct(
        private readonly string $sourcePageId,
        private readonly string $copyAction,
        private readonly array $blockInstructions,
    ) {
    }

    public function getSourcePageId(): string
    {
        return $this->sourcePageId;
    }

    public function getCopyAction(): string
    {
        return $this->copyAction;
    }

    public function shouldOverwrite(): bool
    {
        return $this->copyAction === self::ACTION_OVERWRITE;
    }

    /**
     * @return array<string, BlockCopyInstruction>
     */
    public function getBlockInstructions(): array
    {
        return $this->blockInstructions;
    }

    public function getBlockInstruction(string $blockId): ?BlockCopyInstruction
    {
        return $this->blockInstructions[$blockId] ?? null;
    }
}
