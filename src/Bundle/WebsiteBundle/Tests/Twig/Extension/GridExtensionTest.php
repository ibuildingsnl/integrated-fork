<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Tests\Twig\Extension;

use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Twig\Extension\GridExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class GridExtensionTest extends TestCase
{
    public function testRenderGridResolvesTemplatePerRenderCall(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getMainRequest')->willReturn(null);

        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects(self::exactly(2))
            ->method('locateTemplate')
            ->with('page/grid.html.twig')
            ->willReturnOnConsecutiveCalls('first-grid.twig', 'second-grid.twig');

        $extension = new GridExtension($requestStack, $themeManager);

        $grid = new Grid('main');
        $page = $this->createMock(AbstractPage::class);
        $page->method('getGrid')->with('main')->willReturn($grid);

        $templates = [];
        $environment = $this->createMock(Environment::class);
        $environment
            ->method('render')
            ->willReturnCallback(function (string $template) use (&$templates): string {
                $templates[] = $template;

                return $template;
            });

        $first = $extension->renderGrid($environment, ['page' => $page], 'main');
        $second = $extension->renderGrid($environment, ['page' => $page], 'main');

        self::assertSame('first-grid.twig', $first);
        self::assertSame('second-grid.twig', $second);
        self::assertSame(['first-grid.twig', 'second-grid.twig'], $templates);
    }

    public function testRenderGridUsesTemplateOverrideWhenProvided(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getMainRequest')->willReturn(null);

        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects(self::never())
            ->method('locateTemplate');

        $extension = new GridExtension($requestStack, $themeManager);

        $grid = new Grid('main');
        $page = $this->createMock(AbstractPage::class);
        $page->method('getGrid')->with('main')->willReturn($grid);

        $environment = $this->createMock(Environment::class);
        $environment
            ->expects(self::once())
            ->method('render')
            ->with('override.twig', ['grid' => $grid])
            ->willReturn('ok');

        $result = $extension->renderGrid($environment, ['page' => $page], 'main', [
            'template' => 'override.twig',
        ]);

        self::assertSame('ok', $result);
    }
}
