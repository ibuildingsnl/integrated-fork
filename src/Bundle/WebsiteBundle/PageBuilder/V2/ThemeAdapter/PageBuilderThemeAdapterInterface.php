<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter;

interface PageBuilderThemeAdapterInterface
{
    public function supportsTheme(string $theme): bool;

    public function resolveTemplateForComponent(string $componentType): ?string;
}

