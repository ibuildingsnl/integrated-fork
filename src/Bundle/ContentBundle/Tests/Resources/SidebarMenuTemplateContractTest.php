<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\TwigRenderer;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final class SidebarMenuTemplateContractTest extends TestCase
{
    public function testSidebarMenuSkipsEmptyTopLevelGroupsWithoutVisibleChildren(): void
    {
        $factory = new MenuFactory();
        $menu = $factory->createItem('integrated_menu');
        $menu->addChild('Settings')->setExtra('icon', 'iconoir-settings');

        $html = $this->renderer()->render($menu, ['depth' => 3]);

        $this->assertStringNotContainsString('Settings', $html);
        $this->assertStringNotContainsString('sidebar-sub-menu', $html);
    }

    public function testSidebarMenuKeepsTopLevelGroupsWithVisibleChildren(): void
    {
        $factory = new MenuFactory();
        $menu = $factory->createItem('integrated_menu');
        $menu->addChild('Settings')
            ->setExtra('icon', 'iconoir-settings')
            ->addChild('Channels', ['uri' => '/admin/channels']);

        $html = $this->renderer()->render($menu, ['depth' => 3]);

        $this->assertStringContainsString('Settings', $html);
        $this->assertStringContainsString('Channels', $html);
        $this->assertStringContainsString('sidebar-sub-menu', $html);
    }

    private function renderer(): TwigRenderer
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__.'/../../Resources/views', 'IntegratedContent');
        $loader->addPath(\dirname(__DIR__, 7).'/knplabs/knp-menu/src/Knp/Menu/Resources/views');

        $twig = new Environment($loader);
        $twig->addFilter(new TwigFilter('trans', static fn (string $value): string => $value));
        $twig->addFilter(new TwigFilter('parse_icons', static fn (string $value): string => $value, ['is_safe' => ['html']]));

        return new TwigRenderer(
            $twig,
            '@IntegratedContent/menu/menu.html.twig',
            $this->createStub(MatcherInterface::class)
        );
    }
}
