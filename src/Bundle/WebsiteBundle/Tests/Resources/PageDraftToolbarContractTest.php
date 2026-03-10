<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageDraftToolbarContractTest extends TestCase
{
    public function testToolbarContainsDraftWorkspaceActions(): void
    {
        $toolbar = file_get_contents(__DIR__.'/../../Resources/views/toolbar.html.twig');

        $this->assertIsString($toolbar);
        $this->assertStringContainsString('data-action="integrated-website-page-save-draft"', $toolbar);
        $this->assertStringContainsString('data-action="integrated-website-page-publish"', $toolbar);
        $this->assertStringContainsString('data-action="integrated-website-page-preview-draft"', $toolbar);
        $this->assertStringContainsString('id="integrated-website-draft-status"', $toolbar);
    }

    public function testToolbarPageScriptUsesPageDraftEndpoints(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-page-js.html.twig');
        $css = file_get_contents(__DIR__.'/../../Resources/views/themes/default/objects/toolbar-css.html.twig');

        $this->assertIsString($script);
        $this->assertIsString($css);
        $this->assertStringContainsString('integrated_website_page_draft_save', $script);
        $this->assertStringContainsString('integrated_website_page_draft_publish', $script);
        $this->assertStringContainsString('integrated_website_page_draft_preview_link', $script);
        $this->assertStringContainsString('integrated-website-draft-status', $script);
        $this->assertStringContainsString('payload && payload.conflict', $script);
        $this->assertStringContainsString('is-warning', $script);
        $this->assertStringContainsString('.integrated-draft-status.is-warning', $css);
        $this->assertStringContainsString('.integrated-draft-status.is-error', $css);
    }
}
