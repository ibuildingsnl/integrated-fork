<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\PageBuilder\V2\ThemeAdapter;

use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter\PageBuilderThemeAdapterInterface;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter\ThemeAdapterRegistry;
use PHPUnit\Framework\TestCase;

final class ThemeAdapterRegistryTest extends TestCase
{
    public function testReturnsExactThemeMatchWhenAvailable(): void
    {
        $exact = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return $theme === 'exact-theme';
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                return 'exact';
            }
        };

        $fallback = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return false;
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                return 'fallback';
            }
        };

        $registry = new ThemeAdapterRegistry([$fallback, $exact]);

        self::assertSame($exact, $registry->getAdapter('exact-theme'));
    }

    public function testFallsBackToFirstAdapterWhenNoThemeMatches(): void
    {
        $first = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return false;
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                return 'first';
            }
        };

        $second = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return false;
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                return 'second';
            }
        };

        $registry = new ThemeAdapterRegistry([$first, $second]);

        self::assertSame($first, $registry->getAdapter('unknown-theme'));
    }

    public function testPrefersDefaultAdapterOverFirstFallbackWhenThemeIsUnknown(): void
    {
        $first = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return false;
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                return 'first';
            }
        };

        $default = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return $theme === 'default';
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                return 'default';
            }
        };

        $registry = new ThemeAdapterRegistry([$first, $default]);

        self::assertSame($default, $registry->getAdapter('unknown-theme'));
    }
}
