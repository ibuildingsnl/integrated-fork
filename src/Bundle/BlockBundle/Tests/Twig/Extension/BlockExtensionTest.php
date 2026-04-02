<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Twig\Extension;

use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Bundle\BlockBundle\Service\RuntimeBlockUsageCollector;
use Integrated\Bundle\BlockBundle\Templating\BlockManager;
use Integrated\Bundle\BlockBundle\Twig\Extension\BlockExtension;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Block\BlockInterface;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BlockExtensionTest extends TestCase
{
    public function testFindPagesReturnsEmptyArrayWhenUsageProviderReturnsNull(): void
    {
        $usageProvider = $this->createMock(BlockUsageProvider::class);
        $usageProvider
            ->expects(self::once())
            ->method('getPagesPerBlock')
            ->with('block-id')
            ->willReturn(null);

        $extension = new BlockExtension(
            $this->createMock(BlockManager::class),
            $this->createMock(ThemeManager::class),
            $usageProvider,
            $this->createMock(MetadataFactoryInterface::class),
            $this->createMock(ChannelContextInterface::class),
            $this->createMock(LoggerInterface::class),
            'test',
            new RuntimeBlockUsageCollector()
        );

        $block = $this->createMock(BlockInterface::class);
        $block->method('getId')->willReturn('block-id');

        self::assertSame([], $extension->findPages($block));
    }

    public function testFindContainerBlocksReturnsEmptyArrayWhenUsageProviderReturnsNull(): void
    {
        $usageProvider = $this->createMock(BlockUsageProvider::class);
        $usageProvider
            ->expects(self::once())
            ->method('getContainerBlocksPerBlock')
            ->with('block-id')
            ->willReturn(null);

        $extension = new BlockExtension(
            $this->createMock(BlockManager::class),
            $this->createMock(ThemeManager::class),
            $usageProvider,
            $this->createMock(MetadataFactoryInterface::class),
            $this->createMock(ChannelContextInterface::class),
            $this->createMock(LoggerInterface::class),
            'test',
            new RuntimeBlockUsageCollector()
        );

        $block = $this->createMock(BlockInterface::class);
        $block->method('getId')->willReturn('block-id');

        self::assertSame([], $extension->findContainerBlocks($block));
    }

    public function testFindTemplateUsagesReturnsEmptyArrayWhenUsageProviderReturnsNull(): void
    {
        $usageProvider = $this->createMock(BlockUsageProvider::class);
        $usageProvider
            ->expects(self::once())
            ->method('getTemplateUsagesPerBlock')
            ->with('block-id')
            ->willReturn(null);

        $extension = new BlockExtension(
            $this->createMock(BlockManager::class),
            $this->createMock(ThemeManager::class),
            $usageProvider,
            $this->createMock(MetadataFactoryInterface::class),
            $this->createMock(ChannelContextInterface::class),
            $this->createMock(LoggerInterface::class),
            'test',
            new RuntimeBlockUsageCollector()
        );

        $block = $this->createMock(BlockInterface::class);
        $block->method('getId')->willReturn('block-id');

        self::assertSame([], $extension->findTemplateUsages($block));
    }

    public function testGetBlockCssClassReturnsResolvedCssClassForId(): void
    {
        $resolvedBlock = $this->createMock(BlockInterface::class);
        $resolvedBlock
            ->expects(self::once())
            ->method('getCssClass')
            ->willReturn('custom-class');

        $blockManager = $this->createMock(BlockManager::class);
        $blockManager
            ->expects(self::once())
            ->method('getBlock')
            ->with('block-id')
            ->willReturn($resolvedBlock);

        $extension = new BlockExtension(
            $blockManager,
            $this->createMock(ThemeManager::class),
            $this->createMock(BlockUsageProvider::class),
            $this->createMock(MetadataFactoryInterface::class),
            $this->createMock(ChannelContextInterface::class),
            $this->createMock(LoggerInterface::class),
            'test',
            new RuntimeBlockUsageCollector()
        );

        self::assertSame('custom-class', $extension->getBlockCssClass('block-id'));
    }

    public function testGetBlockCssClassReturnsEmptyStringWhenLookupThrows(): void
    {
        $blockManager = $this->createMock(BlockManager::class);
        $blockManager
            ->expects(self::once())
            ->method('getBlock')
            ->with('block-id')
            ->willThrowException(new \Error('Class does not exist'));

        $extension = new BlockExtension(
            $blockManager,
            $this->createMock(ThemeManager::class),
            $this->createMock(BlockUsageProvider::class),
            $this->createMock(MetadataFactoryInterface::class),
            $this->createMock(ChannelContextInterface::class),
            $this->createMock(LoggerInterface::class),
            'test',
            new RuntimeBlockUsageCollector()
        );

        self::assertSame('', $extension->getBlockCssClass('block-id'));
    }
}
