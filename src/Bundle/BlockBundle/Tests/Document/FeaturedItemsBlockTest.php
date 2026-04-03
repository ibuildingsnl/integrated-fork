<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Document;

use Integrated\Bundle\BlockBundle\Document\Block\Embedded\FeaturedItemsItem;
use Integrated\Bundle\BlockBundle\Document\Block\FeaturedItemsBlock;
use PHPUnit\Framework\TestCase;

final class FeaturedItemsBlockTest extends TestCase
{
    public function testGetItemsReturnsEmptyArrayWhenCollectionHydrationFails(): void
    {
        $block = new FeaturedItemsBlock();

        $collection = new class {
            public function toArray(): array
            {
                throw new \ErrorException('Undefined array key "$id"');
            }
        };

        $property = new \ReflectionProperty($block, 'items');
        $property->setValue($block, $collection);

        self::assertSame([], $block->getItems());
    }

    public function testGetItemsSortsItemsByOrder(): void
    {
        $first = (new FeaturedItemsItem())->setOrder(20);
        $second = (new FeaturedItemsItem())->setOrder(10);

        $block = new FeaturedItemsBlock();
        $block->setItems([$first, $second]);

        self::assertSame([$second, $first], $block->getItems());
    }
}
