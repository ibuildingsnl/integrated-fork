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

        foreach ($grids as $grid) {
            if (!\is_array($grid)) {
                continue;
            }

            $children[] = [
                'type' => 'container',
                'props' => [
                    'id' => (string) ($grid['id'] ?? ''),
                ],
                'children' => [],
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
}
