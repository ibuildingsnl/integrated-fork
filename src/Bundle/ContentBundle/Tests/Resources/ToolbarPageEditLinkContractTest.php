<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ToolbarPageEditLinkContractTest extends TestCase
{
    public function testToolbarBuildsFrontendPageEditLinkAlongsideOpenPage(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/partials/block.toolbar.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% trans %}Open page{% endtrans %}', $template);
        $this->assertStringContainsString('{% trans %}Edit page{% endtrans %}', $template);
        $this->assertStringContainsString("{% set isPageDocument = pageDocument and attribute(pageDocument, 'slug') is not defined %}", $template);
        $this->assertStringContainsString('?integrated_website_edit=1', $template);
        $this->assertStringContainsString('{% set pageOpenUrl = pagePath %}', $template);
        $this->assertStringContainsString("path('integrated_website_enter_session', {'sessionId': app.session.id, 'path': pagePath})", $template);
        $this->assertStringContainsString("path('integrated_website_enter_session'", $template);
        $this->assertStringContainsString('{% if pageEditUrl %}', $template);
    }

    public function testToolbarOnlyRendersPreviewWhenChannelBasedPreviewLinksExist(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/partials/block.toolbar.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% if previewLinks|length > 0 %}', $template);
        $this->assertStringNotContainsString('fallbackPreviewUrl', $template);
        $this->assertStringNotContainsString('No preview is available yet', $template);
        $this->assertStringNotContainsString('View preview', $template);
    }
}
