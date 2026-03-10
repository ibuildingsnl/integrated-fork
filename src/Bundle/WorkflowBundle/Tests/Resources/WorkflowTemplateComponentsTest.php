<?php

declare(strict_types=1);

namespace Integrated\Bundle\WorkflowBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class WorkflowTemplateComponentsTest extends TestCase
{
    public function testWorkflowIndexUsesAdminComponents(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/workflow/index.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("component('integrated_admin:page_title'", $template);
        self::assertStringContainsString("{% component 'integrated_admin:section_card'", $template);
        self::assertStringContainsString("component('integrated_admin:data_table'", $template);
    }
}
