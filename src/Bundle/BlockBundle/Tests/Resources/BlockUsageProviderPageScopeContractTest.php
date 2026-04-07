<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class BlockUsageProviderPageScopeContractTest extends TestCase
{
    public function testUsageProviderIndexesAllPageSubtypesIncludingContentTypePages(): void
    {
        $provider = file_get_contents(__DIR__.'/../../Provider/BlockUsageProvider.php');

        self::assertIsString($provider);
        self::assertStringContainsString('AbstractPage::class', $provider);
        self::assertStringNotContainsString('createQueryBuilder(Page::class)', $provider);
    }
}
