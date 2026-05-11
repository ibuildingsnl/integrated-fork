<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Form\Type;

use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\PageBundle\Form\Type\PageCopyBlocksType;
use Integrated\Bundle\PageBundle\Form\Type\PageCopyBlockType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

final class PageCopyBlocksTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new PageCopyBlocksType(),
                new PageCopyBlockType(),
            ], []),
        ];
    }

    public function testBuildsFormForBlockIdsWithIllegalFormNameCharacters(): void
    {
        $block = new TextBlock();
        $block->setId('dans_colofon_text_!');

        $form = $this->factory->create(PageCopyBlocksType::class, null, [
            'blocks' => [
                'dans_colofon_text_!' => $block,
            ],
            'channel' => 'dans',
            'targetChannel' => 'dans_copy',
        ]);

        self::assertFalse($form->has('block_dans_colofon_text_!'));
        self::assertTrue($form->has('block_dans_colofon_text__'));
        self::assertSame('dans_colofon_text_!', $form->get('block_dans_colofon_text__')->get('sourceBlockId')->getData());
    }
}
