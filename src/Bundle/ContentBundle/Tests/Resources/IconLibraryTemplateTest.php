<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class IconLibraryTemplateTest extends TestCase
{
    public function testContentRoutingImportsIconLibraryRoutes(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('routing/icons.xml', $routing);
        $this->assertStringContainsString('<import resource="@IntegratedContentBundle/Resources/config/routing/icons.xml"', $routing);
    }

    public function testIconRoutingContainsIndexRoute(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing/icons.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('id="integrated_content_icon_index"', $routing);
        $this->assertStringContainsString('path="/icons"', $routing);
        $this->assertStringContainsString('IconController::index', $routing);
    }

    public function testIconTemplateContainsSearchFilterAndCopyMarkers(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/icon/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('id="icon-library-search"', $template);
        $this->assertStringContainsString('data-icon-style-filter', $template);
        $this->assertStringContainsString('data-icon-copy', $template);
        $this->assertStringContainsString('data-icon-segment', $template);
    }

    public function testRelationAndContentTypeFormsLinkToIconLibrary(): void
    {
        $relationType = file_get_contents(__DIR__.'/../../Form/Type/RelationType.php');
        $contentType = file_get_contents(__DIR__.'/../../Form/Type/ContentTypeFormType.php');

        $this->assertIsString($relationType);
        $this->assertIsString($contentType);
        $this->assertStringContainsString('/admin/icons', $relationType);
        $this->assertStringContainsString('/admin/icons', $contentType);
    }

    public function testIconControllerUsesAggressivePrivateCaching(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/IconController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('FilesystemAdapter', $controller);
        $this->assertStringContainsString('setPrivate()', $controller);
        $this->assertStringContainsString('setMaxAge(self::ICON_PAGE_CACHE_TTL)', $controller);
        $this->assertStringContainsString('setLastModified', $controller);
        $this->assertStringContainsString('isNotModified($request)', $controller);
    }
}
