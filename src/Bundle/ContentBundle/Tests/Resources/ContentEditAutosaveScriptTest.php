<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentEditAutosaveScriptTest extends TestCase
{
    public function testUnlockArticleScriptContainsAutosaveHooks(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/unlock_article.js');

        $this->assertIsString($script);
        $this->assertStringContainsString("data-draft-save-url", $script);
        $this->assertStringContainsString('saveDraftIfNeeded', $script);
        $this->assertStringContainsString("method: 'DELETE'", $script);
        $this->assertStringContainsString('A draft was found for this item.', $script);
    }
}
