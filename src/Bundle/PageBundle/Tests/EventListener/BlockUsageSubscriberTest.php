<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Item;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\EventListener\BlockUsageSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

final class BlockUsageSubscriberTest extends TestCase
{
    public function testPrePersistRefreshesDenormalizedBlockIds(): void
    {
        $block = new TextBlock();
        $block->setId('block-a');

        $grid = (new Grid('main'))->setItems([(new Item())->setBlock($block)]);
        $page = (new Page())->setGrids([$grid])->setBlockIds([]);

        $subscriber = new BlockUsageSubscriber();
        $subscriber->prePersist(
            new LifecycleEventArgs(
                $page,
                $this->createMock(DocumentManager::class)
            )
        );

        self::assertSame(['block-a'], $page->getBlockIds());
    }

    public function testPostUpdateInvalidatesBlockUsageCacheForPages(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('delete')
            ->with(BlockUsageProvider::CACHE_KEY)
            ->willReturn(true);

        $subscriber = new BlockUsageSubscriber($cache);
        $subscriber->postUpdate(
            new LifecycleEventArgs(
                new Page(),
                $this->createMock(DocumentManager::class)
            )
        );
    }
}
