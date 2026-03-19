<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class FacebookEditorPreviewContractTest extends TestCase
{
    public function testEditorBuildsDedicatedFacebookPreviewCardsForFallbackEmbeds(): void
    {
        $script = file_get_contents(__DIR__.'/../../../FormTypeBundle/Resources/assets/js/editor.js');
        self::assertIsString($script);

        $this->assertStringContainsString('function buildFacebookEmbedPreview', $script);
        $this->assertStringContainsString('facebook-embed-preview', $script);
        $this->assertStringContainsString("if (data.provider_name === 'Facebook' && isFacebookFallbackEmbed(doc)) {", $script);
        $this->assertStringContainsString('data-facebook-embed-original', $script);
        $this->assertStringContainsString("editor.on('GetContent', function(event) {", $script);
        $this->assertStringContainsString('data.provider_url || data.url || extractedUrl || \'\'', $script);
        $this->assertStringContainsString('data.description || extractedUrl || \'Facebook embed\'', $script);
        $this->assertStringContainsString("if (!node || node.nodeType !== Node.ELEMENT_NODE || node.querySelector('.facebook-embed-preview')) {", $script);
        $this->assertStringNotContainsString("if (!(node instanceof Element) || node.querySelector('.facebook-embed-preview')) {", $script);
        $this->assertStringContainsString("node.dataset.facebookPreviewMetadataLoading = 'true';", $script);
        $this->assertStringContainsString("url: '/admin/_oembed/fetch-data',", $script);
        $this->assertStringContainsString("node.dataset.facebookPreviewMetadataLoaded = 'true';", $script);
    }

    public function testEditorNormalizesAnchorHtmlToExternalUrlBeforeOEmbedCheck(): void
    {
        $script = file_get_contents(__DIR__.'/../../../FormTypeBundle/Resources/assets/js/editor.js');
        self::assertIsString($script);

        $this->assertStringContainsString('function extractEmbeddableUrl', $script);
        $this->assertStringContainsString('const firstLink = container.querySelector(\'a[href]\');', $script);
        $this->assertStringContainsString('let input = extractEmbeddableUrl(args.content.trim());', $script);
        $this->assertStringNotContainsString('console.log(a.href);', $script);
    }
}
