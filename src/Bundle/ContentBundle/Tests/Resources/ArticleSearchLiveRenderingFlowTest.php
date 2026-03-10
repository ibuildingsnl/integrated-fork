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

    public function testLiveComponentUsesAdminFilterGroupForSearchFilters(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/article_search/components/live_component.html.twig');

        self::assertIsString($template);
        self::assertSame(2, substr_count($template, "component('integrated_admin:filter_group'"));
        self::assertStringContainsString("title: this.translations.channels|default('Channels')", $template);
        self::assertStringContainsString("title: this.translations.content_types|default('Content types')", $template);
        self::assertStringContainsString("listClass: 'form-group w-full flex flex-wrap overflow-y-auto'", $template);
        self::assertStringContainsString("listStyle: 'max-height: calc(-35px + 50vh);'", $template);
    }

    public function testCompiledAssetContainsLiveBridgeAndNoVueMount(): void
    {
        $compiled = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/public/article-search.js');

        self::assertIsString($compiled);
        self::assertStringContainsString('data-article-search-apply', $compiled);
        self::assertStringContainsString('data-article-search-root', $compiled);
        self::assertStringContainsString('mceAction:"close"', $compiled);
        self::assertStringNotContainsString('createApp', $compiled);
        self::assertStringNotContainsString('article-search/ArticleSearchComponent.vue', $compiled);
    }
}
