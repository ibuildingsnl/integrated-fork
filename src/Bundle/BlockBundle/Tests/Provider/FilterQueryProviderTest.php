<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Bundle\BlockBundle\Provider\FilterQueryProvider;
use PHPUnit\Framework\TestCase;

final class FilterQueryProviderTest extends TestCase
{
    public function testGetBlocksByChannelQueryBuilderExcludesUsedBlocksWhenUnusedFilterIsEnabled(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder
            ->method('field')
            ->willReturnSelf();
        $builder
            ->method('notEqual')
            ->willReturnSelf();
        $builder
            ->expects(self::once())
            ->method('notIn')
            ->with(['block-a', 'block-b'])
            ->willReturnSelf();

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->with(Block::class)
            ->willReturn($builder);

        $usageProvider = $this->createMock(BlockUsageProvider::class);
        $usageProvider
            ->expects(self::once())
            ->method('getUsedBlockIds')
            ->willReturn(['block-a', 'block-b']);

        $provider = new FilterQueryProvider($documentManager, $usageProvider);

        self::assertSame(
            $builder,
            $provider->getBlocksByChannelQueryBuilder(['unused' => '1'], null)
        );
    }
}
