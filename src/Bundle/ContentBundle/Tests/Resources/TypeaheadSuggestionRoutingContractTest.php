<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class TypeaheadSuggestionRoutingContractTest extends TestCase
{
    public function testTypeaheadSelectUsesMediaGalleryUrlForMediaResults(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/scripts.js');

        $this->assertIsString($script);
        $this->assertStringContainsString('window.location.href = resolveSuggestionUrl(suggestion.data);', $script);
        $this->assertStringContainsString('if (data && data.open_in_media_gallery && data.media_gallery_url)', $script);
        $this->assertStringContainsString('return data.media_gallery_url;', $script);
        $this->assertStringContainsString('return data.url;', $script);
    }
}
