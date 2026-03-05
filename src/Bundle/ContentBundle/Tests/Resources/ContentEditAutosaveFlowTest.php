<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentEditAutosaveFlowTest extends TestCase
{
    public function testRoutingContainsAutosaveEndpoints(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing/content.xml');

        $this->assertIsString($routing);
        $this->assertStringContainsString('id="integrated_content_content_draft_get"', $routing);
        $this->assertStringContainsString('id="integrated_content_content_draft_save"', $routing);
        $this->assertStringContainsString('id="integrated_content_content_draft_delete"', $routing);
        $this->assertStringContainsString('path="/{id}/draft"', $routing);
    }

    public function testEditFormContainsAutosaveDataAttributes(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString("'data-draft-get-url' =>", $controller);
        $this->assertStringContainsString("'data-draft-save-url' =>", $controller);
        $this->assertStringContainsString("'data-draft-delete-url' =>", $controller);
    }

    public function testControllerContainsDraftCrudMethods(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('public function getDraft(Request $request, string $id): JsonResponse', $controller);
        $this->assertStringContainsString('public function saveDraft(Request $request, string $id): JsonResponse', $controller);
        $this->assertStringContainsString('public function deleteDraft(Request $request, string $id): JsonResponse', $controller);
        $this->assertStringContainsString('private function clearCurrentUserDraft(Content $content): void', $controller);
    }
}
