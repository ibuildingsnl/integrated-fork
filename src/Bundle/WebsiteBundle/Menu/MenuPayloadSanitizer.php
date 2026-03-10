<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Menu;

final class MenuPayloadSanitizer
{
    /**
     * @param array<mixed> $menuPayload
     *
     * @return array<int, array<string, mixed>>
     */
    public static function sanitizePayload(array $menuPayload): array
    {
        $menus = [];
        foreach ($menuPayload as $menuArray) {
            $sanitized = self::sanitizeMenuArray((array) $menuArray, true);
            if ($sanitized) {
                $menus[] = $sanitized;
            }
        }

        return $menus;
    }

    /**
     * @param array<mixed> $menuPayload
     *
     * @return array<string, array<string, mixed>>
     */
    public static function mapPayloadByMenuName(array $menuPayload): array
    {
        $menusByName = [];
        foreach (self::sanitizePayload($menuPayload) as $menu) {
            $name = trim((string) ($menu['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $menusByName[$name] = $menu;
        }

        return $menusByName;
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>|null
     */
    private static function sanitizeMenuArray(array $item, bool $isRoot = false): ?array
    {
        $children = [];
        foreach ((array) ($item['children'] ?? []) as $child) {
            $sanitizedChild = self::sanitizeMenuArray((array) $child);
            if ($sanitizedChild) {
                $children[] = $sanitizedChild;
            }
        }
        $item['children'] = $children;

        if ($isRoot) {
            return $item;
        }

        $name = trim((string) ($item['name'] ?? ''));
        $uri = trim((string) ($item['uri'] ?? ''));
        $searchSelection = trim((string) ($item['searchSelection'] ?? ''));
        $hasChildren = \count($children) > 0;

        $isPlaceholder = ($name === '' || $name === '+')
            && ($uri === '' || $uri === '#')
            && $searchSelection === ''
            && !$hasChildren;

        return $isPlaceholder ? null : $item;
    }
}
