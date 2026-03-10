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
        $this->assertStringContainsString("'versions' => \$this->normalizeDraftVersions(\$draft)", $controller);
        $this->assertStringContainsString('private function normalizeDraftVersions(ContentEditDraft $draft): array', $controller);
        $this->assertStringContainsString("'contentUpdatedAt' => \$this->normalizeContentUpdatedAt(\$content)", $controller);
        $this->assertStringContainsString('private function isContentUpdatedAfterBaseline(Content $content, ?string $baseline): bool', $controller);
    }

    public function testToolbarContainsDraftSaveButton(): void
    {
        $toolbar = file_get_contents(__DIR__.'/../../Resources/views/partials/block.toolbar.html.twig');

        $this->assertIsString($toolbar);
        $this->assertStringContainsString('integrated_content_actions_save_draft', $toolbar);
        $this->assertStringNotContainsString('integrated_content_actions_draft_version', $toolbar);
        $this->assertStringNotContainsString('integrated_content_actions_restore_draft_version', $toolbar);
    }

    public function testEditViewContainsDraftVersionControlsInHistorySection(): void
    {
        $editView = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($editView);
        $this->assertStringContainsString('integrated_content_draft_versions_section', $editView);
        $this->assertStringContainsString('integrated_content_actions_draft_version', $editView);
        $this->assertStringContainsString('integrated_content_actions_restore_draft_version', $editView);
        $this->assertStringContainsString('content-history-section', $editView);
    }
}
