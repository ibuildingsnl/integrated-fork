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
        $this->assertStringContainsString('{% set seo = integrated_page_seo(page|default(null)) %}', $template);
        $this->assertStringContainsString('<meta name="robots" content="{{ seo.robots }}">', $template);
        $this->assertStringContainsString('<link rel="canonical" href="{{ seo.canonicalUrl }}" />', $template);
        $this->assertStringContainsString('<meta property="og:title" content="{{ seo.openGraphTitle }}">', $template);
        $this->assertStringContainsString('<meta property="og:description" content="{{ seo.openGraphDescription }}">', $template);
        $this->assertStringContainsString('<meta property="og:image" content="{{ seo.openGraphImageUrl }}">', $template);
        $this->assertStringContainsString('<meta name="twitter:card" content="{{ seo.twitterCard }}">', $template);
    }
}
