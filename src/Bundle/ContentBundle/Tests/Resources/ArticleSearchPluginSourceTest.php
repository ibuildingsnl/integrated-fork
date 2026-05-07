<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ArticleSearchPluginSourceTest extends TestCase
{
    public function testLinkMakerUsesClosestAnchorForNestedFormattedLinks(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/article_search.js');

        self::assertIsString($source);
        self::assertStringContainsString('const findClosestAnchor = function', $source);
        self::assertStringContainsString('let anchorNode = findClosestAnchor(selectedNode);', $source);
        self::assertStringContainsString('if(anchorNode) {', $source);
        self::assertStringContainsString('let anchor = findClosestAnchor(element);', $source);
    }
}
