<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentEditAutosaveFlowTest extends TestCase
{
    public function testDraftAutosaveContractIsConsistentAcrossRoutingControllerTemplatesAndScript(): void
    {
        $routing = file_get_contents(__DIR__.'/../../Resources/config/routing/content.xml');
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');
        $toolbar = file_get_contents(__DIR__.'/../../Resources/views/partials/block.toolbar.html.twig');
        $editView = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/unlock_article.js');

        $this->assertIsString($routing);
        $this->assertIsString($controller);
        $this->assertIsString($toolbar);
        $this->assertIsString($editView);
        $this->assertIsString($script);

        $flags = [
            'routing endpoints' => str_contains($routing, 'id="integrated_content_content_draft_get"')
                && str_contains($routing, 'id="integrated_content_content_draft_save"')
                && str_contains($routing, 'id="integrated_content_content_draft_delete"')
                && str_contains($routing, 'path="/{id}/draft"'),
            'controller form attrs' => str_contains($controller, "'data-draft-get-url' =>")
                && str_contains($controller, "'data-draft-save-url' =>")
                && str_contains($controller, "'data-draft-delete-url' =>")
                && str_contains($controller, "'data-content-updated-at' =>"),
            'controller draft actions' => str_contains($controller, 'public function getDraft(Request $request, string $id): JsonResponse')
                && str_contains($controller, 'public function saveDraft(Request $request, string $id): JsonResponse')
                && str_contains($controller, 'public function deleteDraft(Request $request, string $id): JsonResponse'),
            'toolbar draft action' => str_contains($toolbar, 'integrated_content_actions_save_draft'),
            'edit view draft controls' => str_contains($editView, 'integrated_content_draft_status')
                && str_contains($editView, 'integrated_content_draft_versions_section')
                && str_contains($editView, 'integrated_content_actions_draft_version')
                && str_contains($editView, 'integrated_content_actions_restore_draft_version'),
            'unlock_article draft hooks' => str_contains($script, 'data-draft-save-url')
                && str_contains($script, 'saveDraftIfNeeded')
                && str_contains($script, 'integrated_content_actions_save_draft')
                && str_contains($script, 'integrated_content_actions_draft_version')
                && str_contains($script, 'integrated_content_draft_status'),
        ];

        $baseline = reset($flags);
        foreach ($flags as $part => $enabled) {
            $this->assertSame(
                $baseline,
                $enabled,
                \sprintf(
                    'Draft autosave support is inconsistent: "%s" is %s while the baseline state is %s.',
                    $part,
                    $enabled ? 'enabled' : 'disabled',
                    $baseline ? 'enabled' : 'disabled'
                )
            );
        }
    }
}
