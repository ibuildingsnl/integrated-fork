<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageSeoAnalysisRouteContractTest extends TestCase
{
    public function testPageRoutingExposesSeoContentEndpoint(): void
    {
        $yaml = file_get_contents(__DIR__.'/../../Resources/config/routing/page.yaml');
        $xml = file_get_contents(__DIR__.'/../../Resources/config/routing/page.xml');

        $this->assertIsString($yaml);
        $this->assertIsString($xml);
        $this->assertStringContainsString('integrated_page_page_seo_content:', $yaml);
        $this->assertStringContainsString("path: '/{id}/seo-content'", $yaml);
        $this->assertStringContainsString('route id="integrated_page_page_seo_content"', $xml);
    }

    public function testPageControllerImplementsSeoContentRenderer(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('public function seoContent(Page $page): JsonResponse', $controller);
        $this->assertStringContainsString('$this->container->get(\'request_stack\')', $controller);
        $this->assertStringContainsString('$this->container->get(\'http_kernel\')', $controller);
        $this->assertStringContainsString('Request::create(', $controller);
        $this->assertStringContainsString('$this->buildAbsolutePageUrl($page, $currentRequest)', $controller);
        $this->assertStringContainsString('$kernel->handle($seoRequest, HttpKernelInterface::SUB_REQUEST, false)', $controller);
        $this->assertStringContainsString('extractSeoRenderableContent', $controller);
    }
}
