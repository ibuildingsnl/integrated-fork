<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageEditTemplateSidebarContractTest extends TestCase
{
    public function testEditTemplateUsesPageAndContentFormThemes(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("@IntegratedPage/form/form_div_layout.html.twig', '@IntegratedContent/form/form_div_layout.html.twig", $template);
        $this->assertStringContainsString("form.featuredImage", $template);
        $this->assertStringContainsString("Status", $template);
        $this->assertStringContainsString("Concept", $template);
        $this->assertStringContainsString("Scheduled", $template);
        $this->assertStringContainsString("Expired", $template);
        $this->assertStringContainsString("Published", $template);
        $this->assertStringContainsString("form.disabled", $template);
        $this->assertStringContainsString("['publishAt', 'expireAt']", $template);
        $this->assertStringContainsString("['expireRedirectUrl']", $template);
        $this->assertStringContainsString("{{ form_start(form) }}", $template);
        $this->assertStringContainsString("bundles/integratedintegrated/edit.js", $template);
        $this->assertStringContainsString("bundles/integratedintegrated/edit.css", $template);
        $this->assertStringContainsString("Page settings", $template);
        $this->assertStringContainsString("['canonicalUrl', 'robotsDirective', 'twitterCard', 'paginationNoindexEnabled', 'hideFromSitemap']", $template);
        $this->assertStringNotContainsString("['canonicalUrl', 'robotsDirective', 'twitterCard', 'paginationNoindexEnabled', 'hideFromSitemap', 'disabled']", $template);
        $this->assertStringContainsString("form_row(attribute(form, fieldName), {'style': 'horizontal', 'state': 'show'})", $template);

    }

    public function testNewTemplateMirrorsSidebarStructure(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/new.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("@IntegratedPage/form/form_div_layout.html.twig', '@IntegratedContent/form/form_div_layout.html.twig", $template);
        $this->assertStringContainsString("form.featuredImage", $template);
        $this->assertStringContainsString("Status", $template);
        $this->assertStringContainsString("Concept", $template);
        $this->assertStringContainsString("Scheduled", $template);
        $this->assertStringContainsString("Expired", $template);
        $this->assertStringContainsString("Published", $template);
        $this->assertStringContainsString("form.disabled", $template);
        $this->assertStringContainsString("['publishAt', 'expireAt']", $template);
        $this->assertStringContainsString("['expireRedirectUrl']", $template);
        $this->assertStringContainsString("{{ form_start(form) }}", $template);
        $this->assertStringContainsString("bundles/integratedintegrated/edit.js", $template);
        $this->assertStringContainsString("bundles/integratedintegrated/edit.css", $template);
        $this->assertStringContainsString("Page settings", $template);
        $this->assertStringContainsString("['canonicalUrl', 'robotsDirective', 'twitterCard', 'paginationNoindexEnabled', 'hideFromSitemap']", $template);
        $this->assertStringNotContainsString("['canonicalUrl', 'robotsDirective', 'twitterCard', 'paginationNoindexEnabled', 'hideFromSitemap', 'disabled']", $template);
        $this->assertStringContainsString("{% trans %}New{% endtrans %} {% trans %}page{% endtrans %}", $template);
    }
}
