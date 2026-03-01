<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Twig\Extension;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\Rendering\PageBuilderRenderer;
use Integrated\Bundle\WebsiteBundle\Twig\Extension\GridExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class GridExtensionV2Test extends TestCase
{
    /** @var ThemeManager&MockObject */
    private ThemeManager $themeManager;
    /** @var PageBuilderRenderer&MockObject */
    private PageBuilderRenderer $renderer;
    private Environment $twig;

    protected function setUp(): void
    {
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->renderer = $this->createMock(PageBuilderRenderer::class);
        $this->twig = new Environment(new ArrayLoader([
            'unused-grid-template.html.twig' => '{{ grid.id }}',
        ]));

        $this->themeManager
            ->method('locateTemplate')
            ->with('page/grid.html.twig')
            ->willReturn('unused-grid-template.html.twig');
    }

    public function testRenderGridUsesV2RendererWhenLayoutVersionIs2(): void
    {
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
                                'props' => ['blockId' => 'block-a'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with($this->twig, $page, 'main')
            ->willReturn('<section>v2</section>');

        $extension = new GridExtension(new RequestStack(), $this->themeManager, $this->renderer);
        $result = $extension->renderGrid($this->twig, ['page' => $page], 'main');

        self::assertSame('<section>v2</section>', $result);
    }

    public function testRenderGridFallsBackToLegacyWhenV2PayloadHasNoBlockReferences(): void
    {
        $page = new Page();
        $page->setLayoutVersion(2);
        $page->setLayoutPayload([
            'root' => [
                'type' => 'container',
                'children' => [
                    [
                        'type' => 'container',
                        'props' => ['id' => 'main'],
                        'children' => [],
                    ],
                ],
            ],
        ]);
        $page->addGrid(new Grid('main'));

        $this->renderer
            ->expects($this->never())
            ->method('render');

        $extension = new GridExtension(new RequestStack(), $this->themeManager, $this->renderer);
        $result = $extension->renderGrid($this->twig, ['page' => $page], 'main');

        self::assertSame('main', trim($result));
    }
}
