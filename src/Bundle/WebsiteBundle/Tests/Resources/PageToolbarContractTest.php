<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageToolbarContractTest extends TestCase
{
    public function testToolbarContainsDirectPageSaveAction(): void
    {
        $toolbar = file_get_contents(__DIR__.'/../../Resources/views/toolbar.html.twig');

        $this->assertIsString($toolbar);
        $this->assertStringContainsString('data-action="integrated-website-page-save"', $toolbar);
        $this->assertStringContainsString('Save page', $toolbar);
        $this->assertStringNotContainsString('integrated-website-page-save-draft', $toolbar);
        $this->assertStringNotContainsString('integrated-website-page-preview-draft', $toolbar);
        $this->assertStringNotContainsString('integrated-website-page-publish', $toolbar);
        $this->assertStringNotContainsString('integrated-website-draft-status', $toolbar);
    }

    public function testToolbarPageScriptUsesDirectSaveEndpoints(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-page-js.html.twig');
        $gridScript = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-grid-js.html.twig');

        $this->assertIsString($script);
        $this->assertIsString($gridScript);
        $this->assertStringContainsString('[data-action="integrated-website-page-save"]', $script);
        $this->assertStringContainsString('[data-action="integrated-website-page-save"]', $gridScript);
        $this->assertStringContainsString('integrated_website_menu_save', $script);
        $this->assertStringContainsString('integrated_website_grid_save', $script);
        $this->assertStringContainsString('Integrated.Menu.isDirty(item)', $script);
        $this->assertStringContainsString('if (!menus.length) {', $script);
        $this->assertStringNotContainsString('integrated_website_page_draft_save', $script);
        $this->assertStringNotContainsString('integrated_website_page_draft_publish', $script);
        $this->assertStringNotContainsString('integrated_website_page_draft_preview_link', $script);
    }

    public function testToolbarRestrictsPageButtonsToWebsiteManagers(): void
    {
        $toolbar = file_get_contents(__DIR__.'/../../Resources/views/toolbar.html.twig');

        $this->assertIsString($toolbar);
        $this->assertStringContainsString("{% set canManagePages = is_granted('ROLE_ADMIN') or is_granted('ROLE_WEBSITE_MANAGER') %}", $toolbar);
        $this->assertStringContainsString('{% if layoutEditable and canManagePages %}', $toolbar);
        $this->assertStringNotContainsString('{% if layoutEditable %}', $toolbar);
    }
}
