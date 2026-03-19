<?php

namespace Integrated\Bundle\WorkflowBundle\Tests\Form;

use PHPUnit\Framework\TestCase;

class WorkflowDefinitionStateCollectionTemplateTest extends TestCase
{
    public function testWorkflowStatusUsesTintedBackgroundAndBorderStyle(): void
    {
        $templatePath = \dirname(__DIR__, 2).'/Resources/views/form/form_div_layout.html.twig';
        $template = file_get_contents($templatePath);

        self::assertNotFalse($template);
        self::assertStringContainsString('background-color:{{ field.vars.value.color|alpha(12) }};', $template);
        self::assertStringContainsString('border-color:{{ field.vars.value.color|alpha(45) }};', $template);
        self::assertStringContainsString('color:{{ field.vars.value.color }};', $template);
    }
}
