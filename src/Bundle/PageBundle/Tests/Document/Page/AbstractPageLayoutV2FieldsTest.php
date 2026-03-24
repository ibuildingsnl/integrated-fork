<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Document\Page;

use Integrated\Bundle\PageBundle\Document\Page\Page;
use PHPUnit\Framework\TestCase;

final class AbstractPageLayoutV2FieldsTest extends TestCase
{
    public function testLayoutV2FieldsRoundTrip(): void
    {
        $page = new Page();
        $page->setLayoutVersion(2);
        $page->setLayoutPayload(['root' => ['type' => 'container', 'children' => []]]);
        $page->setLayoutMeta(['migratedAt' => '2026-02-27T00:00:00+00:00']);
        $page->setLegacy(['grids' => [['id' => 'main']]]);

        self::assertSame(2, $page->getLayoutVersion());
        self::assertSame('container', $page->getLayoutPayload()['root']['type']);
        self::assertArrayHasKey('migratedAt', $page->getLayoutMeta());
        self::assertArrayHasKey('grids', $page->getLegacy());
    }
}

