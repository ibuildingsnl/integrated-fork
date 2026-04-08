<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BrandTranslationsTest extends TestCase
{
    public function testDutchTranslationsContainConnectorMissingThemeBlocksLabels(): void
    {
        $translations = file_get_contents(__DIR__.'/../../Resources/translations/messages.nl.xliff');

        self::assertIsString($translations);
        self::assertStringContainsString('<source>Missing theme blocks</source>', $translations);
        self::assertStringContainsString('<target>Ontbrekende theme blocks</target>', $translations);
        self::assertStringContainsString('<source>These blocks are referenced by the current channel theme or its fallback themes, but do not exist yet.</source>', $translations);
        self::assertStringContainsString("<target>Deze blocks worden gebruikt in het huidige kanaalthema of de fallback-thema's, maar bestaan nog niet.</target>", $translations);
        self::assertStringContainsString('<source>Duplicate from existing block</source>', $translations);
        self::assertStringContainsString('<target>Dupliceren vanaf bestaand block</target>', $translations);
        self::assertStringContainsString('<source>Duplicate from %id%</source>', $translations);
        self::assertStringContainsString('<target>Dupliceren vanaf %id%</target>', $translations);
        self::assertStringContainsString('<source>No matching source blocks found.</source>', $translations);
        self::assertStringContainsString('<target>Geen passend bronblock gevonden.</target>', $translations);
    }
}
