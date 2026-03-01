<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\PageBuilder\V2\Migration;

final class LayoutV1ToV2Mapper
{
    /**
     * @param array<string, mixed> $legacyLayout
     *
     * @return array{layoutVersion: int, payload: array<string, mixed>, legacy: array<string, mixed>, meta: array<string, mixed>}
     */
    public function map(array $legacyLayout): array
    {
        $grids = isset($legacyLayout['grids']) && \is_array($legacyLayout['grids']) ? $legacyLayout['grids'] : [];
        $children = [];

        foreach ($grids as $gridIndex => $grid) {
            if (!\is_array($grid)) {
                continue;
            }

            $children[] = [
                'type' => 'container',
                'props' => [
                    'id' => (string) ($grid['id'] ?? ''),
                    'role' => 'grid',
                    'legacyGridIndex' => $gridIndex,
                ],
                'children' => $this->mapItems((array) ($grid['items'] ?? [])),
            ];
        }

        return [
            'layoutVersion' => 2,
            'payload' => [
                'root' => [
                    'type' => 'container',
                    'children' => $children,
                ],
            ],
            'legacy' => [
                'grids' => $grids,
            ],
            'meta' => [
                'migratedBy' => 'pagebuilder:v2:migrate',
                'migratedAt' => gmdate(DATE_ATOM),
            ],
        ];
    }

    /**
     * @param array<int, mixed> $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $position => $item) {
            if (!\is_array($item)) {
                continue;
            }

            $normalized[] = [
                'position' => $position,
                'order' => isset($item['order']) ? (int) $item['order'] : PHP_INT_MAX,
                'item' => $item,
            ];
        }

        usort($normalized, static function (array $left, array $right): int {
            $orderComparison = $left['order'] <=> $right['order'];
            if ($orderComparison !== 0) {
                return $orderComparison;
            }

            return $left['position'] <=> $right['position'];
        });

        $components = [];
        foreach ($normalized as $entry) {
            $item = $entry['item'];
            $node = $this->mapItem($item);
            if ($node !== null) {
                $components[] = $node;
            }
        }

        return $components;
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>|null
     */
    private function mapItem(array $item): ?array
    {
        $blockId = trim((string) ($item['block'] ?? ''));
        if ($blockId !== '') {
            return [
                'type' => 'block_ref',
                'props' => [
                    'blockId' => $blockId,
                ],
            ];
        }

        $row = $item['row'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        $columns = [];
        foreach ((array) ($row['columns'] ?? []) as $columnIndex => $column) {
            if (!\is_array($column)) {
                continue;
            }

            $columns[] = [
                'type' => 'container',
                'props' => [
                    'role' => 'column',
                    'size' => isset($column['size']) ? (int) $column['size'] : 0,
                    'index' => $columnIndex,
                ],
                'children' => $this->mapItems((array) ($column['items'] ?? [])),
            ];
        }

        return [
            'type' => 'container',
            'props' => [
                'role' => 'row',
            ],
            'children' => $columns,
        ];
    }
}
