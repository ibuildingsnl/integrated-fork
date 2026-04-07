<?php

declare(strict_types=1);

namespace Integrated\Bundle\WorkflowBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class WorkflowChangeStateAssetTest extends TestCase
{
    public function testLegacyWorkflowInitializerSkipsContentEditorInlineWorkflowPage(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/workflowChangeState.js');

        self::assertIsString($source);
        self::assertStringContainsString('#content-edit-page[data-inline-workflow-state-init="true"] #content-workflow-section .workflow', $source);
        self::assertStringContainsString('return;', $source);
    }
}
