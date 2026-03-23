<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

final class PageCopyRequest
{
    /**
     * @param array<string, PageCopyInstruction> $pageInstructions
     */
    public function __construct(
        private readonly string $sourceChannelId,
        private readonly string $targetChannelId,
        private readonly array $pageInstructions,
    ) {
    }

    public function getSourceChannelId(): string
    {
        return $this->sourceChannelId;
    }

    public function getTargetChannelId(): string
    {
        return $this->targetChannelId;
    }

    /**
     * @return array<string, PageCopyInstruction>
     */
    public function getPageInstructions(): array
    {
        return $this->pageInstructions;
    }

    public function getPageInstruction(string $pageId): ?PageCopyInstruction
    {
        return $this->pageInstructions[$pageId] ?? null;
    }
}
