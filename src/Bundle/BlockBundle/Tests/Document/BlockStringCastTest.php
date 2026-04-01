<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Document;

use Integrated\Bundle\BlockBundle\Document\Block\Block;
use PHPUnit\Framework\TestCase;

final class BlockStringCastTest extends TestCase
{
    public function testUnsavedBlockCastsToEmptyString(): void
    {
        $block = new class extends Block {
            public function getType()
            {
                return 'test_block';
            }
        };

        self::assertSame('', (string) $block);
    }

    public function testBlockFallsBackToTitleWhenIdIsMissing(): void
    {
        $block = new class extends Block {
            public function getType()
            {
                return 'test_block';
            }
        };

        $block->setTitle('Example');

        self::assertSame('Example', (string) $block);
    }
}
