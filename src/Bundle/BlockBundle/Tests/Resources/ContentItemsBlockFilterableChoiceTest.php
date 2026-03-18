<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentItemsBlockFilterableChoiceTest extends TestCase
{
    public function testContentItemsBlockUsesFilterableContentChoiceType(): void
    {
        $source = file_get_contents(__DIR__.'/../../Document/Block/ContentItemsBlock.php');

        self::assertIsString($source);
        self::assertStringContainsString('Integrated\\Bundle\\FormTypeBundle\\Form\\Type\\FilterableContentChoiceType', $source);
    }
}
