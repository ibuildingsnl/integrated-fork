<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class MediaThumbnailComponentTemplateTest extends TestCase
{
    public function testMediaThumbnailComponentSupportsSolrMediaPathAndMimeFields(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/media/partial/media_thumbnail_component.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('media.image_string is defined', $template);
        self::assertStringContainsString('media.mimetype_string is defined', $template);
        self::assertStringContainsString('{% for value in media.file %}', $template);
    }
}
