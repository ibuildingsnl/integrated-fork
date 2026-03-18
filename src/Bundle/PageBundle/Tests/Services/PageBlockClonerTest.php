<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Services;

use Integrated\Bundle\BlockBundle\Document\Block\InlineTextBlock;
use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageBlockCloner;
use PHPUnit\Framework\TestCase;

final class PageBlockClonerTest extends TestCase
{
    public function testClonesTextBlockWithoutReflection(): void
    {
        $source = new TextBlock();
        $source->setId('source-block');
        $source->setTitle('Source block');
        $source->setContent('Hello');
        $source->setCssClass('hero');

        $cloner = new PageBlockCloner();
        $copiedPage = new Page();
        $copiedPage->setPath('/copy');
        $copiedPage->setLayout('default.html.twig');
        $copiedPage->setTitle('Copy');

        $cloned = $cloner->cloneBlock($source, 'target-block', $copiedPage);

        self::assertInstanceOf(TextBlock::class, $cloned);
        self::assertNotSame($source, $cloned);
        self::assertSame('target-block', $cloned->getId());
        self::assertSame('Source block', $cloned->getTitle());
        self::assertSame('Hello', $cloned->getContent());
        self::assertSame('hero', $cloned->getCssClass());
    }

    public function testClonesInlineTextBlockAgainstCopiedPage(): void
    {
        $sourcePage = new Page();
        $sourcePage->setPath('/source');
        $sourcePage->setLayout('default.html.twig');
        $sourcePage->setTitle('Source');

        $copiedPage = new Page();
        $copiedPage->setPath('/copy');
        $copiedPage->setLayout('default.html.twig');
        $copiedPage->setTitle('Copy');

        $source = new InlineTextBlock($sourcePage);
        $source->setId('inline-source');
        $source->setContent('Inline');

        $cloned = (new PageBlockCloner())->cloneBlock($source, 'inline-copy', $copiedPage);

        self::assertInstanceOf(InlineTextBlock::class, $cloned);
        self::assertSame('inline-copy', $cloned->getId());
        self::assertSame('Inline', $cloned->getContent());
        self::assertSame($copiedPage, $cloned->getPage());
    }

    public function testThrowsForUnsupportedBlockType(): void
    {
        $source = new class extends \Integrated\Bundle\BlockBundle\Document\Block\Block {
            public function getType()
            {
                return 'unsupported';
            }
        };
        $source->setId('unsupported');
        $source->setTitle('Unsupported');

        $targetPage = new Page();
        $targetPage->setPath('/copy');
        $targetPage->setLayout('default.html.twig');
        $targetPage->setTitle('Copy');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported block type');

        (new PageBlockCloner())->cloneBlock($source, 'unsupported-copy', $targetPage);
    }
}
