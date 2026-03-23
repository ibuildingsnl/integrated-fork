<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class FormLayoutAttributeRenderingTest extends TestCase
{
    public function testAttributesBlockSkipsIterableAttributeValues(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% block attributes -%}', $template);
        $this->assertStringContainsString('{%- if attrvalue is iterable -%}', $template);
        $this->assertStringContainsString('{{- attrname }}="{{ attrvalue|trans({}, translation_domain) }}"', $template);
    }
}
