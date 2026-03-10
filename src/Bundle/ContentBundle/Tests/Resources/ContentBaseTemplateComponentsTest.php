<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentBaseTemplateComponentsTest extends TestCase
{
    public function testContentBaseUsesAdminPageTitleAndOptionsToolbarComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/base.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("component('integrated_admin:options_toolbar'", $template);
        self::assertStringContainsString("component('integrated_admin:aside_panel'", $template);
    }

    public function testContentBaseProvidesIconsForFacetPanels(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/base.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("{% set facetIcon = 'folder' %}", $template);
        self::assertStringContainsString("{% set facetIcon = 'network-reverse' %}", $template);
        self::assertStringContainsString("{% set facetIcon = 'empty-page' %}", $template);
        self::assertStringContainsString("{% set facetIcon = 'info-circle' %}", $template);
        self::assertStringContainsString("{% set facetIcon = 'user' %}", $template);
        self::assertStringContainsString("{% set facetIcon = 'edit-pencil' %}", $template);
        self::assertStringContainsString("icon: facetIcon", $template);
        self::assertStringContainsString("icon: 'link'", $template);
    }
}
