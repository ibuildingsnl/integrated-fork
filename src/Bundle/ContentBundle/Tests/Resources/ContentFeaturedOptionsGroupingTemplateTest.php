<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentFeaturedOptionsGroupingTemplateTest extends TestCase
{
    public function testEditTemplateGroupsPremiumAndFeaturedFieldsInsideContentOptions(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% trans %}Content Options{% endtrans %}', $template);
        $this->assertStringContainsString('{% if form.featured is defined %}', $template);
        $this->assertStringContainsString('{{ form_row(form.premium) }}', $template);
        $this->assertStringContainsString("{% set premiumDelayFields = customOptionFields|filter(f => f.vars.name == 'promo-days') %}", $template);
        $this->assertStringContainsString('data-content-option-field-for="{{ form.premium.vars.id }}"', $template);
        $this->assertStringContainsString('{% if form.featured_expiration is defined %}', $template);
        $this->assertStringContainsString('{% trans %}Featured expires in x days{% endtrans %}', $template);
        $this->assertStringContainsString('{{ form_widget(form.featured_expiration, {', $template);
        $this->assertStringContainsString('data-featured-expiration-container', $template);
        $this->assertStringContainsString('initializeFeaturedExpirationVisibility', $template);
        $this->assertStringContainsString("document.getElementById('{{ form.featured is defined ? form.featured.vars.id|e('js') : '' }}')", $template);
        $this->assertStringContainsString("row.vars.name not in ['premium', 'featured', 'featured_expiration']", $template);
        $this->assertStringNotContainsString('iconoir-half-cookie', $template);
        $this->assertStringNotContainsString('<i class="iconoir-star"></i>', $template);
    }

    public function testIframeEditTemplateGroupsPremiumAndFeaturedFieldsInsideContentOptions(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/partial/edit_frame.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% trans %}Content Options{% endtrans %}', $template);
        $this->assertStringContainsString('{{ form_row(form.premium, {', $template);
        $this->assertStringContainsString("{% set premiumDelayFields = customOptionFields|filter(f => f.vars.name == 'promo-days') %}", $template);
        $this->assertStringContainsString('data-content-option-field-for="{{ form.premium.vars.id }}"', $template);
        $this->assertStringContainsString('{% if form.featured is defined %}', $template);
        $this->assertStringContainsString('{% if form.featured_expiration is defined %}', $template);
        $this->assertStringContainsString('data-featured-expiration-container', $template);
        $this->assertStringContainsString('{% trans %}Featured expires in x days{% endtrans %}', $template);
        $this->assertStringContainsString('{{ form_widget(form.featured_expiration, {', $template);
        $this->assertStringNotContainsString('iconoir-half-cookie', $template);
        $this->assertStringNotContainsString('<i class="iconoir-star"></i>', $template);
    }

    public function testIframeEditPageInitializesFeaturedExpirationVisibilityScript(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.iframe.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('initializeFeaturedExpirationVisibility', $template);
        $this->assertStringContainsString("document.addEventListener('turbo:frame-load', initializeFeaturedExpirationVisibility);", $template);
    }
}
