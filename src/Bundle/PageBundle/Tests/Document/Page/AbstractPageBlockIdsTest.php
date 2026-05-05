<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Document\Page;

use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Column;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Item;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Row;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use PHPUnit\Framework\TestCase;

final class AbstractPageBlockIdsTest extends TestCase
{
    public function testStringCastReturnsEmptyStringWhenPathIsNotInitialized(): void
    {
        $page = new Page();

        self::assertSame('', (string) $page);
    }

    public function testUpdateBlockIdsFromGridsCollectsNestedBlocksWithoutDuplicates(): void
    {
        $blockA = new TextBlock();
        $blockA->setId('block-a');

        $blockB = new TextBlock();
        $blockB->setId('block-b');

        $topLevelItem = (new Item())->setBlock($blockA);
        $duplicateItem = (new Item())->setBlock($blockA);
        $nestedItem = (new Item())->setBlock($blockB);

        $column = (new Column())->setItems([$nestedItem, $duplicateItem]);
        $row = (new Row())->setColumns([$column]);
        $rowItem = (new Item())->setRow($row);

        $grid = (new Grid('main'))->setItems([$topLevelItem, $rowItem]);

        $page = (new Page())->setGrids([$grid]);

        self::assertSame(['block-a', 'block-b'], $page->getBlockIds());
    }
}
