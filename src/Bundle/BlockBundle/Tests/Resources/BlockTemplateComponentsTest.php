<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockTemplateComponentsTest extends TestCase
{
    public function testBlockIndexUsesAdminPageTitleComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("component('integrated_admin:filter_search_input'", $template);
    }

    public function testBlockListPartialUsesAdminSectionCardAndDataTable(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/partials/block_list.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
        self::assertStringContainsString("component('integrated_admin:row_actions'", $template);
    }

    public function testBlockSidebarTemplatesUseAdminAsidePanel(): void
    {
        $newTemplate = file_get_contents(__DIR__.'/../../Resources/views/block/new.html.twig');
        $editTemplate = file_get_contents(__DIR__.'/../../Resources/views/block/edit.html.twig');

        self::assertIsString($newTemplate);
        self::assertIsString($editTemplate);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $newTemplate);
        self::assertSame(2, substr_count($editTemplate, "component('integrated_admin:aside_panel'"));
    }

    /**
     * @dataProvider blockCrudTemplateProvider
     */
    public function testBlockCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public static function blockCrudTemplateProvider(): iterable
    {
        yield ['block/new.html.twig'];
        yield ['block/edit.html.twig'];
        yield ['block/delete.html.twig'];
    }

    public function testBlockDeleteUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/delete.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testBlockEditorFormsUseAdminEditFormShellComponent(): void
    {
        $newTemplate = file_get_contents(__DIR__.'/../../Resources/views/block/new.html.twig');
        $editTemplate = file_get_contents(__DIR__.'/../../Resources/views/block/edit.html.twig');

        self::assertIsString($newTemplate);
        self::assertIsString($editTemplate);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $newTemplate);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $editTemplate);
    }

    public function testBlockUsedByTemplateUsesAdminPaginationFooter(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/block/used_by.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
    }
}
