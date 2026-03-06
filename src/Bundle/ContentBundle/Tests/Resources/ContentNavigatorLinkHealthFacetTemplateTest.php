<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentNavigatorLinkHealthFacetTemplateTest extends TestCase
{
    public function testTwigConfigContainsLinkHealthFacetSetting(): void
    {
        $xml = file_get_contents(__DIR__.'/../../Resources/config/twig.xml');

        self::assertIsString($xml);
        self::assertStringContainsString('integrated_content.facet_setting.link_health', $xml);
        self::assertStringContainsString('integrated_content.twig_title.link_health', $xml);
    }
}
