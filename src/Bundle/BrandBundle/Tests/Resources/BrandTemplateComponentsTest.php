<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BrandTemplateComponentsTest extends TestCase
{
    public function testBrandIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/brand/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:row_actions'", $template);
    }

    /**
     * @dataProvider brandCrudTemplateProvider
     */
    public function testBrandCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public function testBrandEditUsesAdminComponentsForChannelList(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/brand/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:row_actions'", $template);
    }

    public function testBrandConfigManageUsesAdminAsidePanelForStatusSidebar(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/brand/config_manage.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $template);
        self::assertStringContainsString("title: 'Status'|trans", $template);
    }

    public function testBrandDeleteUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/brand/delete.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testBrandChannelAndConfigFormsUseAdminEditFormShell(): void
    {
        $channelAddTemplate = file_get_contents(__DIR__.'/../../Resources/views/brand/channel_add.html.twig');
        $channelEditTemplate = file_get_contents(__DIR__.'/../../Resources/views/brand/channel_edit.html.twig');
        $configManageTemplate = file_get_contents(__DIR__.'/../../Resources/views/brand/config_manage.html.twig');

        self::assertIsString($channelAddTemplate);
        self::assertIsString($channelEditTemplate);
        self::assertIsString($configManageTemplate);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $channelAddTemplate);
        self::assertStringContainsString("extraClass: 'edit-form--channel-config'", $channelAddTemplate);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $channelEditTemplate);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $configManageTemplate);
        self::assertStringContainsString("extraClass: 'edit-form--channel-config'", $configManageTemplate);
    }

    public static function brandCrudTemplateProvider(): iterable
    {
        yield ['brand/delete.html.twig'];
        yield ['brand/channel_add.html.twig'];
        yield ['brand/channel_edit.html.twig'];
        yield ['brand/channel_remove.html.twig'];
        yield ['brand/config_manage.html.twig'];
    }
}
