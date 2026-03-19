<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockUsageSourceWiringTest extends TestCase
{
    public function testBlockServicesUseTaggedUsageSources(): void
    {
        $servicesXml = file_get_contents(__DIR__.'/../../Resources/config/services.xml');

        self::assertIsString($servicesXml);
        self::assertStringContainsString('type="tagged_iterator" tag="integrated_block.usage_source"', $servicesXml);
        self::assertStringContainsString('tag name="integrated_block.usage_source"', $servicesXml);
    }
}
