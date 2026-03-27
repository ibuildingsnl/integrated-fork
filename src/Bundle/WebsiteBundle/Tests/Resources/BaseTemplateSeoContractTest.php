<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BaseTemplateSeoContractTest extends TestCase
{
    public function testDefaultBaseTemplateRendersResolvedPageSeoMetadata(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/themes/default/base.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("{% set seo = integrated_page_seo(page|default(null)) %}", $template);
        $this->assertStringContainsString('<meta name="robots" content="{{ seo.robots }}">', $template);
        $this->assertStringContainsString('<link rel="canonical" href="{{ seo.canonicalUrl }}" />', $template);
    }
}
