<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Locator;

use Integrated\Bundle\PageBundle\Locator\LayoutLocator;
use Integrated\Bundle\ThemeBundle\Templating\Theme;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use PHPUnit\Framework\TestCase;

final class LayoutLocatorTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/layout-locator-'.bin2hex(random_bytes(8));
        mkdir($this->tempDir.'/layouts', 0777, true);

        file_put_contents(
            $this->tempDir.'/layouts/sidebar.html.twig',
            "{# Template name: Sidebar #}\n<div></div>\n"
        );
        file_put_contents(
            $this->tempDir.'/layouts/base.html.twig',
            "<!DOCTYPE html>\n<html></html>\n"
        );
        file_put_contents(
            $this->tempDir.'/layouts/fullwidth.html.twig',
            "<div></div>\n"
        );
    }

    protected function tearDown(): void
    {
        @unlink($this->tempDir.'/layouts/sidebar.html.twig');
        @unlink($this->tempDir.'/layouts/base.html.twig');
        @unlink($this->tempDir.'/layouts/fullwidth.html.twig');
        @rmdir($this->tempDir.'/layouts');
        @rmdir($this->tempDir);

        parent::tearDown();
    }

    public function testGetLayoutsUsesTemplateNameCommentOrHumanizedFilenameFallback(): void
    {
        $theme = new Theme('demo', ['@DemoTheme'], []);

        $themeManager = $this->getMockBuilder(ThemeManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getThemes', 'getTheme', 'locateResources'])
            ->getMock();

        $themeManager
            ->method('getThemes')
            ->willReturn(['demo' => $theme]);
        $themeManager
            ->method('getTheme')
            ->with('demo')
            ->willReturn($theme);
        $themeManager
            ->method('locateResources')
            ->with('@DemoTheme')
            ->willReturn([$this->tempDir]);

        $locator = new LayoutLocator($themeManager);

        $layouts = $locator->getLayouts('demo', '/layouts');

        self::assertSame('sidebar.html.twig', $layouts['Sidebar'] ?? null);
        self::assertSame('base.html.twig', $layouts['Base'] ?? null);
        self::assertSame('fullwidth.html.twig', $layouts['Fullwidth'] ?? null);
    }
}
