<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Twig\Extension;

use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
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
            'test'
        );

        $block = $this->createMock(BlockInterface::class);
        $block->method('getId')->willReturn('block-id');

        self::assertSame([], $extension->findPages($block));
    }
}
