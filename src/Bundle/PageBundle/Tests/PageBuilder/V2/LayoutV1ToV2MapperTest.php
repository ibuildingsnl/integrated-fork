<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\PageBuilder\V2;

use Integrated\Bundle\PageBundle\PageBuilder\V2\Migration\LayoutV1ToV2Mapper;
use PHPUnit\Framework\TestCase;

final class LayoutV1ToV2MapperTest extends TestCase
{
    public function testMapsLegacyGridToRootContainerWithLegacyBucket(): void
    {
        $mapper = new LayoutV1ToV2Mapper();
        $mapped = $mapper->map(['grids' => [['id' => 'main', 'items' => []]]]);

        self::assertSame(2, $mapped['layoutVersion']);
        self::assertSame('container', $mapped['payload']['root']['type']);
        self::assertArrayHasKey('grids', $mapped['legacy']);
        self::assertSame('container', $mapped['payload']['root']['children'][0]['type']);
        self::assertSame('main', $mapped['payload']['root']['children'][0]['props']['id']);
    }
}
