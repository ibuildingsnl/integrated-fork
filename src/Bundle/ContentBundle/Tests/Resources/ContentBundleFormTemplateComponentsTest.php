<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentBundleFormTemplateComponentsTest extends TestCase
{
    #[DataProvider('pageTitleProvider')]
    public function testFormTemplateUsesAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    #[DataProvider('editFormShellProvider')]
    public function testEditFormTemplateUsesAdminEditFormShellComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testContentTypeEditUsesAdminAsidePanelForRelationsSidebar(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content_type/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $template);
        self::assertStringContainsString("title: 'Relations'|trans", $template);
        self::assertStringContainsString("icon: 'info-circle'", $template);
        self::assertStringNotContainsString("expanded: true", $template);
        self::assertStringContainsString("withHolder: false", $template);
    }

    public function testContentEditUsesAdminAsidePanelForStableSidebarBlocks(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        self::assertIsString($template);
        self::assertSame(3, substr_count($template, "component('integrated_admin:aside_panel'"));
        self::assertStringContainsString("title: 'Status'|trans", $template);
        self::assertStringContainsString("icon: 'info-circle'", $template);
        self::assertStringContainsString("title: 'Content Options'|trans", $template);
        self::assertStringContainsString("icon: 'settings'", $template);
        self::assertSame(3, substr_count($template, "withHolder: false"));
        self::assertStringContainsString("titleHtml: relationSidebarTitleHtml", $template);
        self::assertStringContainsString("iconoir-{{ relation.vars.attr['data-icon'] }}", $template);
        self::assertStringContainsString("wrapperClass: 'relations'", $template);
        self::assertStringContainsString("containerClass: 'relation'", $template);
        self::assertStringNotContainsString("expanded: true", $template);
    }

    public static function pageTitleProvider(): iterable
    {
        yield ['channel/new.html.twig'];
        yield ['channel/edit.html.twig'];
        yield ['relation/new.html.twig'];
        yield ['relation/edit.html.twig'];
        yield ['content_type/new.html.twig'];
        yield ['content_type/edit.html.twig'];
    }

    public static function editFormShellProvider(): iterable
    {
        yield ['relation/new.html.twig'];
        yield ['relation/edit.html.twig'];
    }
}
