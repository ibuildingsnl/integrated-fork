<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class YoastSeoFieldMappingContractTest extends TestCase
{
    public function testInitializerUsesConfiguredFieldSelectors(): void
    {
        $source = file_get_contents(__DIR__.'/../../YoastSeo/src/index.jsx');

        $this->assertIsString($source);
        $this->assertStringContainsString('configuration.fieldSelectors', $source);
        $this->assertStringContainsString("const selectField = (key) => fieldSelectors[key] ? document.querySelector(fieldSelectors[key]) : null;", $source);
        $this->assertStringNotContainsString('#integrated_content_title', $source);
    }

    public function testYoastHooksAvoidContentOnlySelectors(): void
    {
        $pageContent = file_get_contents(__DIR__.'/../../YoastSeo/src/hooks/usePageContent.js');
        $analysis = file_get_contents(__DIR__.'/../../YoastSeo/src/hooks/useAnalysis.js');
        $app = file_get_contents(__DIR__.'/../../YoastSeo/src/components/IntegratedYoastApp.jsx');
        $seoTab = file_get_contents(__DIR__.'/../../YoastSeo/src/components/SeoTab.jsx');

        $this->assertIsString($pageContent);
        $this->assertIsString($analysis);
        $this->assertIsString($app);
        $this->assertIsString($seoTab);

        $this->assertStringContainsString('editorFieldMapping.title', $pageContent);
        $this->assertStringContainsString('configuration.analysisContentUrl', $pageContent);
        $this->assertStringContainsString('fetch(configuration.analysisContentUrl', $pageContent);
        $this->assertStringContainsString('resolveSeoPlaceholders', $pageContent);
        $this->assertStringContainsString('editorFieldMapping.readabilityScore', $analysis);
        $this->assertStringContainsString('editorFieldMapping.content', $app);
        $this->assertStringContainsString('yoast-seo-placeholder-toolbar', $seoTab);
        $this->assertStringContainsString('seoPlaceholderDefinitions(configuration)', $seoTab);
        $this->assertStringContainsString('url: configuration.pageUrl,', $app);
        $this->assertStringNotContainsString('configuration.pageUrl + configuration.pageUrl', $app);
        $this->assertStringNotContainsString('#integrated_content_title', $pageContent);
        $this->assertStringNotContainsString('#integrated_content_seoMetadata', $analysis);
        $this->assertStringNotContainsString('#integrated_content_content', $app);
    }
}
