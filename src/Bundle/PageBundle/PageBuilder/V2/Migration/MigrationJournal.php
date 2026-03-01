<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\PageBuilder\V2\Migration;

final class MigrationJournal
{
    /**
     * @var array<string, array{status: string, message: string}>
     */
    private array $entries = [];

    public function mark(string $pageId, string $status, string $message = ''): void
    {
        $this->entries[$pageId] = [
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * @return array<string, array{status: string, message: string}>
     */
    public function all(): array
    {
        return $this->entries;
    }
}

