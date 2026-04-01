<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ContentEditAutosaveScriptTest extends TestCase
{
    public function testDraftRestoreEventConsumersMatchTheProducer(): void
    {
        $unlockArticle = file_get_contents(__DIR__.'/../../Resources/assets/js/unlock_article.js');
        $taxonomyCategory = file_get_contents(__DIR__.'/../../Resources/assets/js/taxonomy_category.js');
        $mediaGallerySelection = file_get_contents(__DIR__.'/../../Resources/assets/js/mediagallery_selection.js');

        $this->assertIsString($unlockArticle);
        $this->assertIsString($taxonomyCategory);
        $this->assertIsString($mediaGallerySelection);

        $producerEnabled = str_contains($unlockArticle, 'integrated:draft-restored');

        $this->assertSame(
            $producerEnabled,
            str_contains($taxonomyCategory, 'integrated:draft-restored'),
            'taxonomy_category.js still assumes draft restore events in a different state than unlock_article.js.'
        );

        $this->assertSame(
            $producerEnabled,
            str_contains($mediaGallerySelection, 'integrated:draft-restored'),
            'mediagallery_selection.js still assumes draft restore events in a different state than unlock_article.js.'
        );
    }
}
