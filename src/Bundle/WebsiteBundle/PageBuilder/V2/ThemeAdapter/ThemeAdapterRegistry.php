<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter;

final class ThemeAdapterRegistry
{
    /**
     * @var iterable<PageBuilderThemeAdapterInterface>
     */
    private iterable $adapters;

    /**
     * @param iterable<PageBuilderThemeAdapterInterface> $adapters
     */
    public function __construct(iterable $adapters = [])
    {
        $this->adapters = $adapters;
    }

    public function getAdapter(string $theme): ?PageBuilderThemeAdapterInterface
    {
        $fallback = null;
        $default = null;

        foreach ($this->adapters as $adapter) {
            if ($fallback === null) {
                $fallback = $adapter;
            }

            if ($adapter->supportsTheme($theme)) {
                return $adapter;
            }

            if ($default === null && $adapter->supportsTheme('default')) {
                $default = $adapter;
            }
        }

        return $default ?? $fallback;
    }
}
