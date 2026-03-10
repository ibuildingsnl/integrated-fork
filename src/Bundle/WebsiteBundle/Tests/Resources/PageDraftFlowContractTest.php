<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageDraftFlowContractTest extends TestCase
{
    public function testRoutingImportsPageDraftRoutes(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('routing/page_draft.xml', $routing);
    }

    public function testPageDraftRouteConfigurationContainsAllEndpoints(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing/page_draft.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('id="integrated_website_page_draft_get"', $routing);
        $this->assertStringContainsString('id="integrated_website_page_draft_save"', $routing);
        $this->assertStringContainsString('id="integrated_website_page_draft_delete"', $routing);
        $this->assertStringContainsString('id="integrated_website_page_draft_publish"', $routing);
        $this->assertStringContainsString('id="integrated_website_page_draft_preview_link"', $routing);
    }
}
