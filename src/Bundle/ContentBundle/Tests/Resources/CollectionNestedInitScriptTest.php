<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class CollectionNestedInitScriptTest extends TestCase
{
    public function testSourceInitializesNestedCollectionsWhenAppendingItems(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/collection.js');

        self::assertIsString($source);
        self::assertStringContainsString('function initNestedCollections($context)', $source);
        self::assertStringContainsString("\$context.find('[data-prototype]').each(function()", $source);
        self::assertStringContainsString('init($(this));', $source);
        self::assertStringContainsString('initNestedCollections(item);', $source);
    }

    public function testSourceBindsAddHandlerOnlyToDirectCollectionButton(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/collection.js');

        self::assertIsString($source);
        self::assertStringContainsString("\$collection.children('[data-addfield=\"collection\"]').click(function(event)", $source);
        self::assertStringContainsString('event.stopPropagation();', $source);
    }

    public function testCompiledAssetInitializesNestedCollectionsWhenAppendingItems(): void
    {
        $compiled = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/public/collection.js');

        self::assertIsString($compiled);
        self::assertStringContainsString('function initNestedCollections($context)', $compiled);
        self::assertStringContainsString(".find('[data-prototype]').each(function () {", $compiled);
        self::assertStringContainsString('initNestedCollections(item);', $compiled);
    }

    public function testCompiledAssetBindsAddHandlerOnlyToDirectCollectionButton(): void
    {
        $compiled = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/public/collection.js');

        self::assertIsString($compiled);
        self::assertStringContainsString(".children('[data-addfield=\"collection\"]').click(function (event) {", $compiled);
        self::assertStringContainsString('event.stopPropagation();', $compiled);
    }

    public function testSourceBindsRemoveHandlerOnlyToCurrentCollectionItem(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/collection.js');

        self::assertIsString($source);
        self::assertStringContainsString("elm.find('[data-removefield=\"collection\"]')", $source);
        self::assertStringContainsString(".closest('li').get(0) === elm.get(0);", $source);
        self::assertStringContainsString('event.stopPropagation();', $source);
    }

    public function testCompiledAssetBindsRemoveHandlerOnlyToCurrentCollectionItem(): void
    {
        $compiled = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/public/collection.js');

        self::assertIsString($compiled);
        self::assertStringContainsString(".find('[data-removefield=\"collection\"]').filter(function () {", $compiled);
        self::assertStringContainsString(".closest('li').get(0) === elm.get(0);", $compiled);
        self::assertStringContainsString('event.stopPropagation();', $compiled);
    }
}
