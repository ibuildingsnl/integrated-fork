<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class SeoMetaWidgetPageContractTest extends TestCase
{
    public function testSeoWidgetSupportsPageDocuments(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("attribute(documentNode, 'channel')", $template);
        $this->assertStringContainsString("attribute(documentNode, 'path')", $template);
        $this->assertStringContainsString("attribute(documentNode, 'seoMetadata')", $template);
        $this->assertStringContainsString('pageSeoMetadata.metaTitle|default(pageTitle)', $template);
        $this->assertStringContainsString('{% set documentUrl = pagePath|default("/") %}', $template);
        $this->assertStringContainsString("'fieldSelectors': {", $template);
        $this->assertStringContainsString('{% set seoPlaceholderDefinitions = [', $template);
        $this->assertStringContainsString("'seoPlaceholders': seoPlaceholderDefinitions", $template);
        $this->assertStringContainsString("'token': '%%title%%'", $template);
        $this->assertStringContainsString("'label': 'Titel'", $template);
        $this->assertStringContainsString("'analysisContentUrl': documentNode.id is defined and documentNode.id is not null and documentNode.slug is not defined ? path('integrated_page_page_seo_content', {'id': documentNode.id}) : null", $template);
        $this->assertStringContainsString("form.parent.path is defined ? '#' ~ form.parent.path.vars.id : null", $template);
        $this->assertStringContainsString("'content': form.parent.content is defined ? '#' ~ form.parent.content.vars.id : null", $template);
        $this->assertStringContainsString("{% integrated_stylesheets mode='append' 'bundles/integratedcontent/js/main.css' %}", $template);
        $this->assertStringNotContainsString('seo-placeholder-toolbar', $template);
    }
}
