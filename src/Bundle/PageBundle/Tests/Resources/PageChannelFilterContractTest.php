<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageChannelFilterContractTest extends TestCase
{
    public function testNoneChannelFilterSkipsEmptyKnownWebsiteChannelSet(): void
    {
        $source = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        self::assertIsString($source);
        self::assertStringContainsString('$knownWebsiteChannelIds = $this->getAllExistingWebsiteChannelIds();', $source);
        self::assertStringContainsString('if ($selectedChannelIds === [] && $knownWebsiteChannelIds === [])', $source);
        self::assertStringContainsString('if ($knownWebsiteChannelIds !== [])', $source);
        self::assertStringContainsString('$builder->expr()->field(\'channel.$id\')->notIn($knownWebsiteChannelIds)', $source);
    }
}
