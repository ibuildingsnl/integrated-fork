<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\PageBuilder\V2\Migration;

final class SnapshotStore
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $snapshots = [];

    /**
     * @param array<string, mixed> $snapshot
     */
    public function put(string $snapshotId, array $snapshot): void
    {
        $this->snapshots[$snapshotId] = $snapshot;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $snapshotId): ?array
    {
        return $this->snapshots[$snapshotId] ?? null;
    }
}

