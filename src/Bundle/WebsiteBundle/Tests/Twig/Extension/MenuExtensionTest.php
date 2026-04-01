<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Twig\Extension;

use Integrated\Bundle\MenuBundle\Matcher\RecursiveActiveMatcher;
use Integrated\Bundle\MenuBundle\Menu\DatabaseMenuFactory;
use Integrated\Bundle\MenuBundle\Provider\IntegratedMenuProvider;
use Integrated\Bundle\WebsiteBundle\Twig\Extension\MenuExtension;
use Knp\Menu\MenuFactory;
use Knp\Menu\Twig\Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class MenuExtensionTest extends TestCase
{
    /** @var IntegratedMenuProvider&MockObject */
    private IntegratedMenuProvider $provider;
    /** @var DatabaseMenuFactory&MockObject */
    private DatabaseMenuFactory $factory;
    /** @var Helper&MockObject */
    private Helper $helper;
    /** @var RecursiveActiveMatcher&MockObject */
    private RecursiveActiveMatcher $matcher;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(IntegratedMenuProvider::class);
        $this->factory = $this->createMock(DatabaseMenuFactory::class);
        $this->helper = $this->createMock(Helper::class);
        $this->matcher = $this->createMock(RecursiveActiveMatcher::class);
    }

    public function testRenderMenuUsesDraftPreviewPayloadWhenPresentOnRequest(): void
    {
        $request = Request::create('https://example.test/preview');
        $request->attributes->set('integrated_website_draft_menu_payload', [
            'main' => [
                'name' => 'main',
                'children' => [
                    [
                        'id' => 'menu-item-1',
                        'name' => 'Preview item',
                        'uri' => '/preview-item',
                        'children' => [],
                    ],
                ],
            ],
        ]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getMainRequest')->willReturn($request);

        $menu = (new MenuFactory())->createItem('main');

        $this->factory
            ->expects($this->once())
            ->method('fromArray')
            ->with($this->callback(static function (array $payload): bool {
                return ($payload['name'] ?? null) === 'main'
                    && \count((array) ($payload['children'] ?? [])) === 1;
            }))
            ->willReturn($menu);

        $this->provider->expects($this->never())->method('has');
        $this->provider->expects($this->never())->method('get');

        $this->matcher->expects($this->once())->method('setActive')->with($menu);
        $this->helper
            ->expects($this->once())
            ->method('render')
            ->with($menu, $this->arrayHasKey('template'))
            ->willReturn('<nav>draft</nav>');

        $extension = new MenuExtension(
            $this->provider,
            $this->factory,
            $this->helper,
            $this->matcher,
            $requestStack,
            'menu.html.twig'
        );

        $output = $extension->renderMenu([], 'main', ['depth' => 2]);

        self::assertSame('<nav>draft</nav>', $output);
    }
}
