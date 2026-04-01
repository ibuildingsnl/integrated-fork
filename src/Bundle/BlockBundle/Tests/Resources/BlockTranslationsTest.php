<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockTranslationsTest extends TestCase
{
    public function testDutchTranslationsContainBlockFilterLabels(): void
    {
        $translations = file_get_contents(__DIR__.'/../../Resources/translations/messages.nl.xliff');

        self::assertIsString($translations);
        self::assertStringContainsString('<source>Filter by block name</source>', $translations);
        self::assertStringContainsString('<target>Filter op bloknaam</target>', $translations);
        self::assertStringContainsString('<source>Unused</source>', $translations);
        self::assertStringContainsString('<target>Ongebruikt</target>', $translations);
    }
}
