<?php

declare(strict_types=1);

namespace Integrated\Bundle\ChannelBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ChannelTemplateComponentsTest extends TestCase
{
    public function testConfigIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/config/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
        self::assertStringContainsString("component('integrated_admin:row_actions'", $template);
    }

    public function testChannelOptionsFormThemeUsesAdminSectionCardsForSocialConnectorLayout(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/options.html.twig');

        self::assertIsString($template);
        self::assertSame(3, substr_count($template, "{% component 'integrated_admin:section_card'"));
        self::assertStringContainsString("component('integrated_admin:status_badge'", $template);
    }

    /**
     * @dataProvider configCrudTemplateProvider
     */
    public function testConfigCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    /**
     * @dataProvider configSidebarTemplateProvider
     */
    public function testConfigSidebarTemplatesUseAdminAsidePanel(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $template);
        self::assertStringNotContainsString("expanded: true", $template);
        self::assertStringNotContainsString("wrapperClass: 'show'", $template);
    }

    public function testConfigDeleteUsesAdminEditFormShellComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/config/delete.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testConfigEditorTemplatesUseAdminEditFormShell(): void
    {
        $newTemplate = file_get_contents(__DIR__.'/../../Resources/views/config/new.html.twig');
        $editTemplate = file_get_contents(__DIR__.'/../../Resources/views/config/edit.html.twig');

        self::assertIsString($newTemplate);
        self::assertIsString($editTemplate);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $newTemplate);
        self::assertStringContainsString("extraClass: 'edit-form--channel-config'", $newTemplate);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $editTemplate);
        self::assertStringContainsString("extraClass: 'edit-form--channel-config'", $editTemplate);
    }

    public static function configCrudTemplateProvider(): iterable
    {
        yield ['config/new.html.twig'];
        yield ['config/edit.html.twig'];
        yield ['config/delete.html.twig'];
    }

    public static function configSidebarTemplateProvider(): iterable
    {
        yield ['config/new.html.twig'];
        yield ['config/edit.html.twig'];
    }
}
