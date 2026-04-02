<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Service;

final class RuntimeBlockUsageCollector
{
    /**
     * @var array<string, array{id: string, title: string, type: string}>
     */
    private array $blocks = [];

    public function register(string $id, ?string $title = null, ?string $type = null): void
    {
        $blockId = trim($id);
        if ($blockId === '') {
            return;
        }

        if (!isset($this->blocks[$blockId])) {
            $this->blocks[$blockId] = [
                'id' => $blockId,
                'title' => '',
                'type' => '',
            ];
        }

        $normalizedTitle = trim((string) $title);
        if ($normalizedTitle !== '') {
            $this->blocks[$blockId]['title'] = $normalizedTitle;
        }

        $normalizedType = trim((string) $type);
        if ($normalizedType !== '') {
            $this->blocks[$blockId]['type'] = $normalizedType;
        }
    }

    /**
     * @return array<int, array{id: string, title: string, type: string}>
     */
    public function all(): array
    {
        $blocks = array_values($this->blocks);

        usort(
            $blocks,
            static function (array $left, array $right): int {
                $leftLabel = strtolower(trim($left['title']) !== '' ? $left['title'] : $left['id']);
                $rightLabel = strtolower(trim($right['title']) !== '' ? $right['title'] : $right['id']);

                return $leftLabel <=> $rightLabel;
            }
        );

        return $blocks;
    }
}
