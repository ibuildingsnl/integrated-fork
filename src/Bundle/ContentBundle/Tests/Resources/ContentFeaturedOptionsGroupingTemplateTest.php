<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentFeaturedOptionsGroupingTemplateTest extends TestCase
{
    public function testEditTemplateGroupsFeaturedExpirationUnderFeatured(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("{% if form.featured is defined %}", $template);
        $this->assertStringContainsString("{% trans %}Featured{% endtrans %}", $template);
        $this->assertStringContainsString("{% if form.featured_expiration is defined %}", $template);
        $this->assertStringContainsString("{{ form_row(form.featured_expiration) }}", $template);
        $this->assertStringContainsString('data-featured-expiration-container', $template);
        $this->assertStringContainsString('initializeFeaturedExpirationVisibility', $template);
        $this->assertStringContainsString("row.vars.name not in ['premium', 'featured', 'featured_expiration']", $template);
    }

    public function testIframeEditTemplateGroupsFeaturedExpirationUnderFeatured(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/partial/edit_frame.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("{% if form.featured is defined %}", $template);
        $this->assertStringContainsString("{% if form.featured_expiration is defined %}", $template);
        $this->assertStringContainsString('data-featured-expiration-container', $template);
        $this->assertStringContainsString("{{ form_row(form.featured_expiration, { 'style': 'inline', 'label_col': 'w-full', 'widget_col': 'w-full' }) }}", $template);
    }

    public function testIframeEditPageInitializesFeaturedExpirationVisibilityScript(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.iframe.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('initializeFeaturedExpirationVisibility', $template);
        $this->assertStringContainsString("document.addEventListener('turbo:frame-load', initializeFeaturedExpirationVisibility);", $template);
    }
}
