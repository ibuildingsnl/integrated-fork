<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentEditorResponsiveAsideContractTest extends TestCase
{
    public function testPublicationSettingsAsideKeepsEditorToolbarClearOnSmallerScreens(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/sass/components/_publication_settings.scss');

        self::assertIsString($source);
        self::assertStringContainsString('@include media(max-lg) {', $source);
        self::assertStringContainsString('top: 96px;', $source);
        self::assertStringContainsString('height: calc(100vh - 96px);', $source);
    }

    public function testSeoAsideUsesResponsiveOffsetAndFullWidthSlideOnSmallScreens(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/sass/components/_seo_settings.scss');

        self::assertIsString($source);
        self::assertStringContainsString('@include media(max-lg) {', $source);
        self::assertStringContainsString('@include media(max-sm) {', $source);
        self::assertStringContainsString('transform: translateX(100%);', $source);
        self::assertStringContainsString('top: 96px;', $source);
        self::assertStringContainsString('height: calc(100vh - 96px);', $source);
    }
}
