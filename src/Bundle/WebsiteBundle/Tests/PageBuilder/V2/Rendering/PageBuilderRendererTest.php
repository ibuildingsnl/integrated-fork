<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\PageBuilder\V2\Rendering;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\Rendering\PageBuilderRenderer;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter\PageBuilderThemeAdapterInterface;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\ThemeAdapter\ThemeAdapterRegistry;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class PageBuilderRendererTest extends TestCase
{
    public function testRendersOnlyRequestedGridSubtreeWhenGridNodeExists(): void
    {
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->method('getActiveTheme')
            ->willReturn('test-theme');

        $adapter = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return $theme === 'test-theme';
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                if ($componentType === 'container') {
                    return 'container.html.twig';
                }

                if ($componentType === 'block_ref') {
                    return 'block_ref.html.twig';
                }

                return null;
            }
        };

        $renderer = new PageBuilderRenderer($themeManager, new ThemeAdapterRegistry([$adapter]));
        $twig = new Environment(new ArrayLoader([
            'container.html.twig' => '<container id="{{ component.props.id|default("root") }}">{{ children_html|raw }}</container>',
            'block_ref.html.twig' => '<block id="{{ component.props.blockId|default("") }}"></block>',
        ]));

        $page = new Page();
        $page->setLayoutVersion(2);
        $page->setLayoutPayload([
            'root' => [
                'type' => 'container',
                'children' => [
                    [
                        'type' => 'container',
                        'props' => ['id' => 'main'],
                        'children' => [
                            [
                                'type' => 'block_ref',
                                'props' => ['blockId' => 'main-block'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'container',
                        'props' => ['id' => 'sidebar'],
                        'children' => [
                            [
                                'type' => 'block_ref',
                                'props' => ['blockId' => 'sidebar-block'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $output = $renderer->render($twig, $page, 'main');

        self::assertStringContainsString('main-block', $output);
        self::assertStringNotContainsString('sidebar-block', $output);
    }

    public function testFallsBackToRootWhenRequestedGridNodeDoesNotExist(): void
    {
        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->method('getActiveTheme')
            ->willReturn('test-theme');

        $adapter = new class implements PageBuilderThemeAdapterInterface {
            public function supportsTheme(string $theme): bool
            {
                return $theme === 'test-theme';
            }

            public function resolveTemplateForComponent(string $componentType): ?string
            {
                return $componentType === 'container' ? 'container.html.twig' : null;
            }
        };

        $renderer = new PageBuilderRenderer($themeManager, new ThemeAdapterRegistry([$adapter]));
        $twig = new Environment(new ArrayLoader([
            'container.html.twig' => '<container>{{ children_html|raw }}</container>',
        ]));

        $page = new Page();
        $page->setLayoutVersion(2);
        $page->setLayoutPayload([
            'root' => [
                'type' => 'container',
                'children' => [],
            ],
        ]);

        $output = $renderer->render($twig, $page, 'missing-grid');

        self::assertSame('<container></container>', $output);
    }
}
