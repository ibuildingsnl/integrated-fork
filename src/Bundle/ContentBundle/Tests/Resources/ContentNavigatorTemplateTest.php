<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentNavigatorTemplateTest extends TestCase
{
    public function testTemplateContainsTurboFrameAccessibilityMarkers(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/base.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('id="content-navigator"', $template);
        $this->assertStringContainsString('data-result-count="', $template);
        $this->assertStringContainsString('id="content-navigator-live-region"', $template);
        $this->assertStringContainsString('aria-live="polite"', $template);
        $this->assertStringContainsString('role="button"', $template);
        $this->assertStringContainsString('aria-expanded=', $template);
    }

    public function testIndexTemplateContainsLiveLockMarkers(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('data-lock-resource-type="', $template);
        $this->assertStringContainsString('data-lock-resource-id="', $template);
        $this->assertStringContainsString('data-lock-slot', $template);
        $this->assertStringContainsString('integrated_content_content_locks_status', $template);
        $this->assertStringContainsString("document.addEventListener('turbo:load', initLockPolling);", $template);
        $this->assertStringContainsString("document.addEventListener('turbo:frame-load', function(event)", $template);
    }

    public function testIndexTemplatesKeepEditLinksPrefetchable(): void
    {
        $indexTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index.html.twig');
        $weekTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index_week.html.twig');
        $navDropdownTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/navdropdowns.html.twig');

        $this->assertIsString($indexTemplate);
        $this->assertIsString($weekTemplate);
        $this->assertIsString($navDropdownTemplate);

        $this->assertStringNotContainsString('<tbody id="post-list" data-turbo-prefetch="false">', $indexTemplate);
        $this->assertStringContainsString('href="{{ editLink }}" data-turbo-frame="_top">', $indexTemplate);
        $this->assertStringContainsString("path('integrated_content_content_delete'", $indexTemplate);
        $this->assertStringContainsString('data-turbo-prefetch="false"', $indexTemplate);

        $this->assertStringNotContainsString('<div class="week columns" data-turbo-prefetch="false">', $weekTemplate);
        $this->assertStringContainsString('class="calendar-item {{ calendarState }}" href="{{ editLink }}" target="_blank" data-turbo-frame="_top"', $weekTemplate);
        $this->assertStringNotContainsString('class="calendar-item {{ calendarState }}" href="{{ editLink }}" target="_blank" data-turbo-frame="_top" data-turbo-prefetch="false"', $weekTemplate);

        $this->assertStringNotContainsString('class="list list-large list-border js-assigned-list" data-turbo-prefetch="false"', $navDropdownTemplate);
        $this->assertStringContainsString("path('integrated_content_content_edit', {id: doc.type_id}) }}\" data-turbo-frame=\"_top\"", $navDropdownTemplate);
        $this->assertStringNotContainsString("path('integrated_content_content_edit', {id: doc.type_id}) }}\" data-turbo-frame=\"_top\" data-turbo-prefetch=\"false\"", $navDropdownTemplate);
    }

    public function testRoutingContainsLiveLockStatusEndpoint(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing/content.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('id="integrated_content_content_locks_status"', $routing);
        $this->assertStringContainsString('path="/locks-status" methods="POST"', $routing);
    }

    public function testControllerSkipsLockAcquisitionForPrefetchRequests(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('X-Sec-Purpose', $controller);
        $this->assertStringContainsString('private function isPrefetchRequest(Request $request): bool', $controller);
        $this->assertStringContainsString('$this->isPrefetchRequest($request)', $controller);
        $this->assertStringContainsString('$this->createUnlockedLocking()', $controller);
        $this->assertStringContainsString('$this->getLock($content, self::CONTENT_LOCK_TIMEOUT_SECONDS)', $controller);
    }
}
