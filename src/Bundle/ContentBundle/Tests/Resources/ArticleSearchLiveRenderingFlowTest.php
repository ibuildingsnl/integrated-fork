<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ArticleSearchLiveRenderingFlowTest extends TestCase
{
    public function testSourceTemplateUsesLiveComponent(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/views/article_search/article_search.html.twig');

        self::assertIsString($source);
        self::assertStringContainsString("component('integrated_content_article_search'", $source);
        self::assertStringNotContainsString('article-search-component', $source);
    }

    public function testLiveComponentTemplateDoesNotRequireLinkTitle(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/article_search/components/live_component.html.twig');

        self::assertIsString($template);
        self::assertStringNotContainsString('{% set linkTitleInvalid = this.linkTitle is empty %}', $template);
        self::assertStringNotContainsString('require_link_title', $template);
        self::assertStringNotContainsString("title=\"{% if linkTitleInvalid %}", $template);
    }

    public function testCompiledAssetContainsLiveBridgeAndNoVueMount(): void
    {
        $compiled = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/public/article-search.js');
        $editorCompiled = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/public/edit.js');

        self::assertIsString($compiled);
        self::assertIsString($editorCompiled);
        self::assertStringContainsString('data-article-search-apply', $compiled);
        self::assertStringContainsString('data-article-search-root', $compiled);
        self::assertStringContainsString("mceAction: 'close'", $compiled);
        self::assertStringNotContainsString('state.linkTitle.length===0', $compiled);
        self::assertStringNotContainsString('title="${safeTitle}"', $compiled);
        self::assertStringContainsString("setAttrib(selectedNode, 'title', data.title || null)", $editorCompiled);
        self::assertStringNotContainsString('createApp', $compiled);
        self::assertStringNotContainsString('article-search/ArticleSearchComponent.vue', $compiled);
    }
}
