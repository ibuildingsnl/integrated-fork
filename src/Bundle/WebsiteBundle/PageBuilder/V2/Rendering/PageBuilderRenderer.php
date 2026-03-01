<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\PageBuilder\V2\Rendering;

use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter\PageBuilderThemeAdapterInterface;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter\ThemeAdapterRegistry;
use Twig\Environment;

class PageBuilderRenderer
{
    private ThemeManager $themeManager;
    private ThemeAdapterRegistry $themeAdapterRegistry;

    public function __construct(ThemeManager $themeManager, ThemeAdapterRegistry $themeAdapterRegistry)
    {
        $this->themeManager = $themeManager;
        $this->themeAdapterRegistry = $themeAdapterRegistry;
    }

    public function render(Environment $environment, AbstractPage $page, string $gridId): string
    {
        $payload = $page->getLayoutPayload();
        $root = $payload['root'] ?? null;
        if (!\is_array($root)) {
            return '';
        }

        $theme = $this->themeManager->getActiveTheme();
        $adapter = $this->themeAdapterRegistry->getAdapter($theme);
        if (!$adapter instanceof PageBuilderThemeAdapterInterface) {
            return '';
        }

        return $this->renderNode($environment, $root, $adapter, $gridId);
    }

    /**
     * @param array<string, mixed> $node
     */
    private function renderNode(Environment $environment, array $node, PageBuilderThemeAdapterInterface $adapter, string $gridId): string
    {
        $type = (string) ($node['type'] ?? '');
        if ($type === '') {
            return '';
        }

        $template = $adapter->resolveTemplateForComponent($type);
        if ($template === null) {
            return '';
        }

        $children = [];
        foreach ((array) ($node['children'] ?? []) as $child) {
            if (\is_array($child)) {
                $children[] = $this->renderNode($environment, $child, $adapter, $gridId);
            }
        }

        return $environment->render($template, [
            'component' => $node,
            'children' => $children,
            'children_html' => implode('', $children),
            'gridId' => $gridId,
        ]);
    }
}

