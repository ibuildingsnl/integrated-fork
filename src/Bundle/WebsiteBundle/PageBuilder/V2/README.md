# PageBuilder V2 (WebsiteBundle)

This directory contains the rendering layer for PageBuilder V2 in `IntegratedWebsiteBundle`.

## Why this exists

PageBuilder stores a theme-agnostic JSON layout (`layoutVersion = 2`), while frontend themes may use different CSS/grid systems.

The adapter layer is needed to:

- keep one stable layout schema for all themes;
- let each theme decide its own Twig/component markup;
- avoid hard-coding framework details in shared core logic;
- provide a safe fallback when no specific theme adapter matches.

Without this layer, theme-specific markup (for example Tailwind vs Bootstrap classes) leaks into core and breaks cross-theme behavior.

## Runtime flow

1. `PageBuilderRenderer` asks `ThemeManager` for the active theme id.
2. `ThemeAdapterRegistry` scans registered adapters and calls `supportsTheme($themeId)`.
3. The first matching adapter is used to resolve templates per component type.
4. If no adapter matches, registry falls back to:
   - an adapter that supports `default`, or
   - the first registered adapter.

## Contracts in this folder

- `ThemeAdapter/PageBuilderThemeAdapterInterface.php`:
  - `supportsTheme(string $theme): bool`
  - `resolveTemplateForComponent(string $componentType): ?string`
- `ThemeAdapter/ThemeAdapterRegistry.php`: adapter selection + fallback policy.
- `ThemeAdapter/DefaultThemeAdapter.php`: default/core component template map.
- `Rendering/PageBuilderRenderer.php`: renders V2 payload nodes using the selected adapter.

## Adding a theme adapter

1. Implement `PageBuilderThemeAdapterInterface` in your theme bundle.
2. Return `true` for all real theme ids used in `integrated_theme.themes` config.
3. Tag the service with `integrated_website.pagebuilder.theme_adapter`.
4. Add/update tests for `supportsTheme()` and template resolution.

## Important maintenance note

If a theme id is missing in `supportsTheme()`, the renderer will fall back to the default adapter.
That typically causes wrong frontend/editor classes (for example Bootstrap `col-sm-*` in a Tailwind theme).
