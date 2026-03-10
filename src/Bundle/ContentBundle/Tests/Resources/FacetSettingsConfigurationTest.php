<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class FacetSettingsConfigurationTest extends TestCase
{
    public function testChannelFacetIsNotExpandedByDefault(): void
    {
        $config = file_get_contents(__DIR__.'/../../Resources/config/twig.xml');

        self::assertIsString($config);
        self::assertStringContainsString('<service id="integrated_content.facet_setting.channel"', $config);
        self::assertStringContainsString('<argument>channels</argument>', $config);
        self::assertStringContainsString('<argument type="expression">false</argument>', $config);
    }

    public function testPropertiesFacetHasNoExplicitExpandedOverride(): void
    {
        $config = file_get_contents(__DIR__.'/../../Resources/config/twig.xml');

        self::assertIsString($config);
        self::assertStringNotContainsString('facet_setting.properties', $config);
        self::assertStringNotContainsString('<argument>properties</argument>', $config);
    }
}
