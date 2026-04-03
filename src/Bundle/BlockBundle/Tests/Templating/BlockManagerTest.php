<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Templating;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Templating\BlockManager;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Block\BlockHandlerRegistryInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class BlockManagerTest extends TestCase
{
    public function testGetBlockCachesRepositoryLookupById(): void
    {
        $expected = $this->createMock(Block::class);

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects(self::once())
            ->method('find')
            ->with('block-id')
            ->willReturn($expected);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Block::class)
            ->willReturn($repository);

        $manager = new BlockManager(
            $this->createMock(BlockHandlerRegistryInterface::class),
            $this->createMock(ThemeManager::class),
            $documentManager,
            $this->createMock(Environment::class)
        );

        self::assertSame($expected, $manager->getBlock('block-id'));
        self::assertSame($expected, $manager->getBlock('block-id'));
    }
}
