<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class CheckboxSwitcherSidebarRenderingTest extends TestCase
{
    public function testCheckboxSwitcherRowSupportsSidebarWrapperMarkup(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/tailwind.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% block checkbox_switcher_row %}', $template);
        $this->assertStringContainsString('{% if style == \'sidebar\' %}', $template);
        $this->assertStringContainsString('class="aside-item-wrapper {{ form.vars.name }}', $template);
        $this->assertStringContainsString('<div class="aside-item-header">', $template);
        $this->assertStringContainsString('<div class="checkbox-switcher">{{ checkboxdata|raw }}</div>', $template);
        $this->assertStringContainsString("{% if style != 'sidebar' and (align_with_widget is defined or attr.align_with_widget is defined) %}", $template);
    }
}
