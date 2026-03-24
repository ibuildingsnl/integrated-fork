<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter;

final class DefaultThemeAdapter implements PageBuilderThemeAdapterInterface
{
    private const MAP = [
        'container' => '@IntegratedWebsite/themes/default/pagebuilder/components/container.html.twig',
        'block_ref' => '@IntegratedWebsite/themes/default/pagebuilder/components/block_ref.html.twig',
    ];

    public function supportsTheme(string $theme): bool
    {
        return $theme === 'default';
    }

    public function resolveTemplateForComponent(string $componentType): ?string
    {
        return self::MAP[$componentType] ?? null;
    }
}

