<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentDraftStatusUxContractTest extends TestCase
{
    public function testDraftStatusRegionMatchesUnlockArticleHooks(): void
    {
        $editView = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/unlock_article.js');

        $this->assertIsString($editView);
        $this->assertIsString($script);

        $statusRegionEnabled = str_contains($editView, 'id="integrated_content_draft_status"');

        $this->assertSame(
            $statusRegionEnabled,
            str_contains($editView, 'aria-live="polite"'),
            'The draft status region and its aria-live behaviour are out of sync.'
        );

        $this->assertSame(
            $statusRegionEnabled,
            str_contains($script, 'setDraftStatus('),
            'unlock_article.js draft status updates are out of sync with the draft status region.'
        );

        $this->assertSame(
            $statusRegionEnabled,
            str_contains($script, 'integrated_content_draft_status'),
            'unlock_article.js status selectors are out of sync with the draft status region.'
        );
    }
}
