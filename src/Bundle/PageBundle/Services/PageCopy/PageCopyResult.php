<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

final class PageCopyResult
{
    /**
     * @param list<string> $skippedExistingPaths
     */
    public function __construct(
        private readonly int $copiedPages,
        private readonly array $skippedExistingPaths = [],
    ) {
    }

    public function getCopiedPages(): int
    {
        return $this->copiedPages;
    }

    public function hasSkippedExistingPages(): bool
    {
        return $this->skippedExistingPaths !== [];
    }

    /**
     * @return list<string>
     */
    public function getSkippedExistingPaths(): array
    {
        return array_values(array_unique($this->skippedExistingPaths));
    }
}
