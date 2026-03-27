<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class UnlockArticleScriptTest extends TestCase
{
    public function testTinyMceChangeRequiresDirtyEditorState(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/unlock_article.js');

        $this->assertIsString($script);
        $this->assertStringContainsString("typeof editor.isDirty === 'function' && !editor.isDirty()", $script);
        $this->assertStringContainsString("form.data('changed', true);", $script);
    }
}
