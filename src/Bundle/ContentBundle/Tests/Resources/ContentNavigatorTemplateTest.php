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
        $this->assertStringContainsString("component('integrated_admin:aside_panel'", $template);
        $this->assertStringContainsString(
            "path('integrated_content_search_selection_delete', {'id': selection.id}) }}\"",
            $template
        );
        $this->assertStringContainsString('data-turbo-frame="_top"', $template);
    }

    public function testCalendarSwitcherUsesBoundedCalendarLimit(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/base.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("queryParams|merge({view: 'week', page: 1, limit: 100})", $template);
        $this->assertStringNotContainsString("queryParams|merge({view: 'week', page: 1, limit: 10000})", $template);
    }

    public function testIndexTemplateContainsLiveLockMarkers(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/index.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("component('integrated_admin:data_table'", $template);
        $this->assertStringContainsString("component('integrated_admin:pagination_footer'", $template);
        $this->assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        $this->assertStringContainsString('tbodyAttributes: \'id="post-list"\'', $template);
        $this->assertStringContainsString('data-lock-resource-type="', $template);
        $this->assertStringContainsString('data-lock-resource-id="', $template);
        $this->assertStringContainsString('data-lock-slot', $template);
        $this->assertStringContainsString('integrated_content_content_locks_status', $template);
        $this->assertStringContainsString("document.addEventListener('turbo:load', initLockPolling);", $template);
        $this->assertStringContainsString("document.addEventListener('turbo:frame-load', function(event)", $template);
        $this->assertStringContainsString("component('integrated_admin:row_actions'", $template);
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

    public function testIndexTemplatesKeepChannelFallbackForMissingFacetBrands(): void
    {
        $indexTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index.html.twig');
        $weekTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/index_week.html.twig');

        $this->assertIsString($indexTemplate);
        $this->assertIsString($weekTemplate);

        $this->assertStringContainsString('{% set document = integrated_document(content) %}', $indexTemplate);
        $this->assertStringContainsString('{% elseif document.channels is defined and document.channels is not null %}', $indexTemplate);
        $this->assertStringContainsString('{% set document = integrated_document(content) %}', $weekTemplate);
        $this->assertStringContainsString('{% if brandNames is empty and document.channels is defined and document.channels is not null %}', $weekTemplate);
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

    public function testControllerShowsSaveOnlyForEditableExistingSearchSelections(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString(
            "'buttons' => \$newSelection || !\$editableSelection ? ['create'] : ['save']",
            $controller
        );
        $this->assertStringNotContainsString(
            "'buttons' => \$newSelection || !\$editableSelection ? ['create'] : ['save', 'create']",
            $controller
        );
    }

    public function testTopbarSearchFormPreservesActiveFilterQueryParams(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/partials/block.search.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('app.request.query.all', $template);
        $this->assertStringContainsString("key not in ['q', 'page']", $template);
        $this->assertStringContainsString('hidden_query_field', $template);
    }

    public function testSidebarMenuDefaultsToContentOpenState(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/menu/menu.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("item.name is same as('Content')", $template);
        $this->assertStringContainsString("state = shouldOpen ? 'sidebar-sub-menu show' : 'sidebar-sub-menu'", $template);
    }

    public function testGlobalScriptPersistsSidebarMenuScrollPosition(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/global.js');

        $this->assertIsString($script);
        $this->assertStringContainsString("const SIDEBAR_MENU_SCROLL_KEY = 'integrated.sidebarMenu.scrollTop.v2';", $script);
        $this->assertStringContainsString('function restoreSidebarMenuScrollPosition', $script);
        $this->assertStringContainsString('function persistSidebarMenuScrollPosition', $script);
        $this->assertStringContainsString("document.addEventListener('turbo:before-render', persistSidebarMenuScrollPosition);", $script);
        $this->assertStringContainsString("window.addEventListener('beforeunload', persistSidebarMenuScrollPosition);", $script);
    }
}
