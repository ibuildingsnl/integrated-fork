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
        $this->assertStringContainsString("Published", $template);
        $this->assertStringContainsString("form.disabled", $template);
        $this->assertStringContainsString("{{ form_start(form) }}", $template);
        $this->assertStringContainsString("Page settings", $template);
        $this->assertStringNotContainsString("['canonicalUrl', 'robotsDirective', 'twitterCard', 'paginationNoindexEnabled', 'disabled']", $template);
        $this->assertStringContainsString("form_row(attribute(form, fieldName), {'style': 'horizontal', 'state': 'show'})", $template);

        $toolbar = file_get_contents(__DIR__.'/../../../ContentBundle/Resources/views/partials/block.toolbar.html.twig');
        $this->assertIsString($toolbar);
        $this->assertStringContainsString("{% trans %}Open page{% endtrans %}", $toolbar);
        $this->assertStringContainsString("target=\"_blank\"", $toolbar);
    }

    public function testNewTemplateMirrorsSidebarStructure(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/page/new.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString("@IntegratedPage/form/form_div_layout.html.twig', '@IntegratedContent/form/form_div_layout.html.twig", $template);
        $this->assertStringContainsString("form.featuredImage", $template);
        $this->assertStringContainsString("Status", $template);
        $this->assertStringContainsString("Concept", $template);
        $this->assertStringContainsString("Published", $template);
        $this->assertStringContainsString("form.disabled", $template);
        $this->assertStringContainsString("{{ form_start(form) }}", $template);
        $this->assertStringContainsString("Page settings", $template);
        $this->assertStringNotContainsString("['canonicalUrl', 'robotsDirective', 'twitterCard', 'paginationNoindexEnabled', 'disabled']", $template);
        $this->assertStringContainsString("{% trans %}New{% endtrans %} {% trans %}page{% endtrans %}", $template);
    }
}
