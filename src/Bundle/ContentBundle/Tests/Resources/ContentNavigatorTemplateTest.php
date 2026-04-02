<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentNavigatorTemplateTest extends TestCase
{
    public function testIndexTemplateUsesSolrNativeStatusLogic(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringNotContainsString('status_logic.html.twig', $template);
        $this->assertStringContainsString('{% set published = content.published|default(false) %}', $template);
        $this->assertStringContainsString("{% set isPublishable = published in [true, 1, '1', 'true'] and channelCount > 0 %}", $template);
        $this->assertStringContainsString('{% set hasPublicationDate = content.pub_time is defined %}', $template);
        $this->assertStringContainsString('{% set isPlanned = isPublishable and hasPublicationDate and not hasStarted %}', $template);
        $this->assertStringContainsString('bundles/integratedcontent/js/content_index_lock_polling.js', $template);
    }

    public function testIndexTemplatesUseSolrNativeChannelFallbackForMissingFacetBrands(): void
    {
        $indexTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index.html.twig');
        $weekTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index_week.html.twig');

        $this->assertIsString($indexTemplate);
        $this->assertIsString($weekTemplate);

        $this->assertStringNotContainsString('{% set document = integrated_document(content) %}', $indexTemplate);
        $this->assertStringContainsString("{% set brandNames = content.facet_brands|default([])|filter(name => name is not empty and name != 'None') %}", $indexTemplate);
        $this->assertStringContainsString('{% set fallbackWebsiteChannelIds = content.website_channel_ids_string|default([])|filter(id => id is not empty and id != \'None\') %}', $indexTemplate);
        $this->assertStringContainsString('{% set fallbackWebsiteChannelFavicons = content.website_channel_favicon_paths_string|default([]) %}', $indexTemplate);
        $this->assertStringContainsString('{% if faviconPath is not empty %}', $indexTemplate);
        $this->assertStringContainsString('image(faviconPath).cropResize(36, 36)', $indexTemplate);
        $this->assertStringNotContainsString('{% set brandNames = content.facet_brands|default([]) %}', $indexTemplate);
        $this->assertStringNotContainsString('{% set document = integrated_document(content) %}', $weekTemplate);
        $this->assertStringContainsString("{% set brandNames = content.facet_brands|default([])|filter(name => name is not empty and name != 'None') %}", $weekTemplate);
        $this->assertStringContainsString('{% set fallbackWebsiteChannelIds = content.website_channel_ids_string|default([])|filter(id => id is not empty and id != \'None\') %}', $weekTemplate);
        $this->assertStringContainsString('{% set fallbackWebsiteChannelFavicons = content.website_channel_favicon_paths_string|default([]) %}', $weekTemplate);
        $this->assertStringContainsString('{% if faviconPath is not empty %}', $weekTemplate);
        $this->assertStringContainsString('image(faviconPath).cropResize(36, 36)', $weekTemplate);
        $this->assertStringNotContainsString('{% set brandNames = content.facet_brands|default([]) %}', $weekTemplate);
    }

    public function testIndexTemplatesOnlyRenderSeoUiWhenCurrentItemsContainSeoMetadata(): void
    {
        $indexTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index.html.twig');
        $weekTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index_week.html.twig');

        $this->assertIsString($indexTemplate);
        $this->assertIsString($weekTemplate);

        $this->assertStringContainsString('{% set showSeoColumn = false %}', $indexTemplate);
        $this->assertStringContainsString('{% set seoCandidateType = contentTypes|filter(t => t.id == seoColumnCandidate.type_name)|first %}', $indexTemplate);
        $this->assertStringContainsString("seoCandidateType.hasField('seoMetadata')", $indexTemplate);
        $this->assertStringContainsString('{% if showSeoColumn %}', $indexTemplate);
        $this->assertStringContainsString('<span>SEO</span>', $indexTemplate);
        $this->assertStringContainsString("type.hasField('seoMetadata')", $indexTemplate);

        $this->assertStringContainsString('{% set showSeoColumn = false %}', $weekTemplate);
        $this->assertStringContainsString('{% set seoCandidateType = contentTypes|filter(t => t.id == seoColumnCandidate.type_name)|first %}', $weekTemplate);
        $this->assertStringContainsString("seoCandidateType.hasField('seoMetadata')", $weekTemplate);
        $this->assertStringContainsString('{% if showSeoColumn %}', $weekTemplate);
        $this->assertStringContainsString('<span class="btn btn-white no-icon seo-button readability-', $weekTemplate);
        $this->assertStringContainsString("type.hasField('seoMetadata')", $weekTemplate);
    }
}
