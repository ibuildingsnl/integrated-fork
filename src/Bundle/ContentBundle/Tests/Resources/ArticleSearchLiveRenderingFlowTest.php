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

    public function testArticleSearchSourceUsesLiveBridgeAndNoVueMount(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/article_search_live.js');
        $editorSource = file_get_contents(__DIR__.'/../../Resources/assets/js/article_search.js');

        self::assertIsString($source);
        self::assertIsString($editorSource);
        self::assertStringContainsString("const ROOT_SELECTOR = '[data-article-search-root]';", $source);
        self::assertStringContainsString("const APPLY_SELECTOR = '[data-article-search-apply]';", $source);
        self::assertStringContainsString("mceAction: 'close'", $source);
        self::assertStringContainsString('const title = normalizeOptionalTitle(state.linkTitle);', $source);
        self::assertStringNotContainsString('linkTitle.length === 0', $source);
        self::assertStringNotContainsString('createApp', $source);
        self::assertStringNotContainsString('ArticleSearchComponent.vue', $source);
        self::assertStringContainsString("editor.dom.setAttrib(selectedNode, 'title', data.title || null);", $editorSource);
    }
}
