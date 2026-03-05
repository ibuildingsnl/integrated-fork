<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;

class IconController extends AbstractController
{
    private const ICON_CSS_PATH = __DIR__.'/../../IntegratedBundle/Resources/public/iconoir.css';
    private const ICON_PARSE_CACHE_TTL = 2592000; // 30 days
    private const ICON_PAGE_CACHE_TTL = 86400; // 24 hours

    /** @var string[]|null */
    private static ?array $iconNames = null;

    private static ?FilesystemAdapter $cache = null;

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $icons = $this->getIconNames();
        $regular = array_values(
            array_filter(
                $icons,
                static fn (string $icon): bool => !str_ends_with($icon, '-solid')
            )
        );
        $solid = array_values(
            array_filter(
                $icons,
                static fn (string $icon): bool => str_ends_with($icon, '-solid')
            )
        );

        $response = $this->render('@IntegratedContent/icon/index.html.twig', [
            'iconFamilies' => [
                [
                    'key' => 'all',
                    'label' => 'All icons',
                    'count' => \count($icons),
                    'segments' => $this->buildSegments($icons),
                ],
                [
                    'key' => 'regular',
                    'label' => 'Regular',
                    'count' => \count($regular),
                    'segments' => $this->buildSegments($regular),
                ],
                [
                    'key' => 'solid',
                    'label' => 'Solid',
                    'count' => \count($solid),
                    'segments' => $this->buildSegments($solid),
                ],
            ],
        ]);

        $lastModifiedAt = (new \DateTimeImmutable())->setTimestamp($this->getIconCssLastModifiedTimestamp());

        $response->setPrivate();
        $response->setMaxAge(self::ICON_PAGE_CACHE_TTL);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('stale-while-revalidate', (string) self::ICON_PAGE_CACHE_TTL);
        $response->setLastModified($lastModifiedAt);

        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }

    /**
     * @return string[]
     */
    private function getIconNames(): array
    {
        if (self::$iconNames !== null) {
            return self::$iconNames;
        }

        $cacheKey = 'icon_names_'.md5(
            (string) $this->getIconCssLastModifiedTimestamp().'|'.(string) (@filesize(self::ICON_CSS_PATH) ?: 0)
        );

        $cache = $this->getCache();
        $icons = $cache->get($cacheKey, function (ItemInterface $item): array {
            $item->expiresAfter(self::ICON_PARSE_CACHE_TTL);

            return $this->extractIconNamesFromCss();
        });

        self::$iconNames = $icons;

        return self::$iconNames;
    }

    private function getCache(): FilesystemAdapter
    {
        if (self::$cache === null) {
            self::$cache = new FilesystemAdapter('integrated_content_icon_library', 0, sys_get_temp_dir());
        }

        return self::$cache;
    }

    private function getIconCssLastModifiedTimestamp(): int
    {
        $mtime = @filemtime(self::ICON_CSS_PATH);
        if ($mtime === false || $mtime <= 0) {
            return time();
        }

        return (int) $mtime;
    }

    /**
     * @return string[]
     */
    private function extractIconNamesFromCss(): array
    {
        $contents = @file_get_contents(self::ICON_CSS_PATH);
        if (!\is_string($contents) || $contents === '') {
            return [];
        }

        preg_match_all('/\\.iconoir-([a-z0-9-]+):{1,2}before/i', $contents, $matches);
        $icons = array_values(array_unique($matches[1]));
        sort($icons, \SORT_NATURAL);

        return $icons;
    }

    /**
     * @param string[] $icons
     *
     * @return array<string, string[]>
     */
    private function buildSegments(array $icons): array
    {
        $segments = [
            'A - F' => [],
            'G - L' => [],
            'M - R' => [],
            'S - Z' => [],
            '0 - 9' => [],
        ];

        foreach ($icons as $icon) {
            $firstCharacter = strtoupper($icon[0] ?? '');

            if ($firstCharacter >= 'A' && $firstCharacter <= 'F') {
                $segments['A - F'][] = $icon;
                continue;
            }
            if ($firstCharacter >= 'G' && $firstCharacter <= 'L') {
                $segments['G - L'][] = $icon;
                continue;
            }
            if ($firstCharacter >= 'M' && $firstCharacter <= 'R') {
                $segments['M - R'][] = $icon;
                continue;
            }
            if ($firstCharacter >= 'S' && $firstCharacter <= 'Z') {
                $segments['S - Z'][] = $icon;
                continue;
            }

            $segments['0 - 9'][] = $icon;
        }

        return array_filter(
            $segments,
            static fn (array $segmentIcons): bool => \count($segmentIcons) > 0
        );
    }
}
