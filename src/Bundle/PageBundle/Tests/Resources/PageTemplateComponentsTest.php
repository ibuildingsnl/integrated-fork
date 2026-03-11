<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageTemplateComponentsTest extends TestCase
{
    public function testPageIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
        self::assertStringContainsString("component('integrated_admin:filter_search_input'", $template);
        self::assertStringContainsString("component('integrated_admin:alert_box'", $template);
        self::assertStringContainsString("component('integrated_admin:row_actions'", $template);
        self::assertStringContainsString("component('integrated_admin:status_badge'", $template);
    }

    /**
     * @dataProvider pageCrudTemplateProvider
     */
    public function testPageCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public function testPageEditUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testPageNewUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/new.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testPageDeleteUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/delete.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testPageCopyUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/copy.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
        self::assertStringContainsString("editorWrapperClass: 'editor-wrapper-full'", $template);
    }

    public function testContentTypePageEditUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content_type_page/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public static function pageCrudTemplateProvider(): iterable
    {
        yield ['page/new.html.twig'];
        yield ['page/edit.html.twig'];
        yield ['page/delete.html.twig'];
        yield ['page/copy.html.twig'];
        yield ['content_type_page/edit.html.twig'];
    }
}
