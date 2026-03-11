<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class UserTemplateComponentsTest extends TestCase
{
    public function testUserIndexUsesAdminPageTitleAndOptionsToolbar(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/user/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("component('integrated_admin:options_toolbar'", $template);
        self::assertStringContainsString("component('integrated_admin:filter_search_input'", $template);
    }

    /**
     * @dataProvider indexTemplateProvider
     */
    public function testIndexTemplateUsesAdminComponents(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:row_actions'", $template);
    }

    public static function indexTemplateProvider(): iterable
    {
        yield ['group/index.html.twig'];
        yield ['scope/index.html.twig'];
        yield ['ip_list/index.html.twig'];
    }

    /**
     * @dataProvider crudTemplateProvider
     */
    public function testCrudTemplatesUseAdminPageTitleComponent(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
    }

    public static function crudTemplateProvider(): iterable
    {
        yield ['group/new.html.twig'];
        yield ['group/edit.html.twig'];
        yield ['group/delete.html.twig'];
        yield ['scope/new.html.twig'];
        yield ['scope/edit.html.twig'];
        yield ['scope/delete.html.twig'];
        yield ['ip_list/new.html.twig'];
        yield ['ip_list/edit.html.twig'];
        yield ['ip_list/delete.html.twig'];
        yield ['profile/index.html.twig'];
        yield ['two_factor/delete.html.twig'];
        yield ['user/new.html.twig'];
        yield ['user/edit.html.twig'];
        yield ['user/delete.html.twig'];
        yield ['user/delete_account.html.twig'];
    }

    public function testUserListPartialUsesAdminDataTableComponent(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/user/partials/user_list.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
        self::assertStringContainsString("component('integrated_admin:row_actions'", $template);
        self::assertStringContainsString("'No users found for the current filters.'|trans", $template);
    }

    /**
     * @dataProvider editFormShellTemplateProvider
     */
    public function testSimpleUserFormsUseAdminEditFormShell(string $relativePath): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/'.$relativePath);

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
    }

    public function testProfileUsesAdminSectionCardForTwoFactorBlock(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/profile/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
    }

    public function testGroupEditUsesAdminSectionCardForUserManagement(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/group/edit.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:edit_form_shell'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:selection_modal'", $template);
    }

    public function testUserSidebarTemplatesUseAdminAsidePanel(): void
    {
        $newTemplate = file_get_contents(__DIR__.'/../../Resources/views/user/new.html.twig');
        $editTemplate = file_get_contents(__DIR__.'/../../Resources/views/user/edit.html.twig');

        self::assertIsString($newTemplate);
        self::assertIsString($editTemplate);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $newTemplate);
        self::assertSame(2, substr_count($editTemplate, "component('integrated_admin:aside_panel'"));
    }

    public static function editFormShellTemplateProvider(): iterable
    {
        yield ['group/new.html.twig'];
        yield ['group/delete.html.twig'];
        yield ['scope/new.html.twig'];
        yield ['scope/edit.html.twig'];
        yield ['scope/delete.html.twig'];
        yield ['ip_list/new.html.twig'];
        yield ['ip_list/edit.html.twig'];
        yield ['ip_list/delete.html.twig'];
        yield ['two_factor/delete.html.twig'];
        yield ['user/new.html.twig'];
        yield ['user/edit.html.twig'];
        yield ['user/delete.html.twig'];
        yield ['user/delete_account.html.twig'];
    }
}
