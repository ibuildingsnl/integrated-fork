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

    public function testMapsOrderedBlockItemsToBlockReferences(): void
    {
        $mapper = new LayoutV1ToV2Mapper();
        $mapped = $mapper->map([
            'grids' => [[
                'id' => 'main',
                'items' => [
                    ['order' => 20, 'block' => 'block-b'],
                    ['order' => 10, 'block' => 'block-a'],
                ],
            ]],
        ]);

        $gridChildren = $mapped['payload']['root']['children'][0]['children'];

        self::assertSame('block_ref', $gridChildren[0]['type']);
        self::assertSame('block-a', $gridChildren[0]['props']['blockId']);
        self::assertSame('block_ref', $gridChildren[1]['type']);
        self::assertSame('block-b', $gridChildren[1]['props']['blockId']);
    }

    public function testMapsNestedRowsAndColumnsToContainerTree(): void
    {
        $mapper = new LayoutV1ToV2Mapper();
        $mapped = $mapper->map([
            'grids' => [[
                'id' => 'main',
                'items' => [[
                    'order' => 1,
                    'row' => [
                        'columns' => [
                            [
                                'size' => 8,
                                'items' => [
                                    ['order' => 1, 'block' => 'block-left'],
                                ],
                            ],
                            [
                                'size' => 4,
                                'items' => [
                                    ['order' => 1, 'block' => 'block-right'],
                                ],
                            ],
                        ],
                    ],
                ]],
            ]],
        ]);

        $row = $mapped['payload']['root']['children'][0]['children'][0];

        self::assertSame('container', $row['type']);
        self::assertSame('row', $row['props']['role']);
        self::assertCount(2, $row['children']);
        self::assertSame('column', $row['children'][0]['props']['role']);
        self::assertSame(8, $row['children'][0]['props']['size']);
        self::assertSame('block-left', $row['children'][0]['children'][0]['props']['blockId']);
        self::assertSame('block-right', $row['children'][1]['children'][0]['props']['blockId']);
    }
}
