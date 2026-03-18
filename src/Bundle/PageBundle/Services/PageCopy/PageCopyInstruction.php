<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

final class PageCopyInstruction
{
    /**
     * @param array<string, BlockCopyInstruction> $blockInstructions
     */
    public function __construct(
        private readonly string $sourcePageId,
        private readonly array $blockInstructions,
    ) {
    }

    public function getSourcePageId(): string
    {
        return $this->sourcePageId;
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
