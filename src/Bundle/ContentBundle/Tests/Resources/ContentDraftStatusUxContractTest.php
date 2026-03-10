<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentDraftStatusUxContractTest extends TestCase
{
    public function testToolbarContainsDraftStatusRegion(): void
    {
        $toolbar = file_get_contents(__DIR__.'/../../Resources/views/partials/block.toolbar.html.twig');

        $this->assertIsString($toolbar);
        $this->assertStringContainsString('id="integrated_content_draft_status"', $toolbar);
        $this->assertStringContainsString('aria-live="polite"', $toolbar);
    }

    public function testAutosaveScriptContainsVisibleStatusHooks(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/unlock_article.js');

        $this->assertIsString($script);
        $this->assertStringContainsString('setDraftStatus(', $script);
        $this->assertStringContainsString('integrated_content_draft_status', $script);
        $this->assertStringNotContainsString("console.error('Draft save failed'", $script);
    }
}
