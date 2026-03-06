<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Document\Block;

use Integrated\Bundle\ContentBundle\Document\Block\FormBlock;
use PHPUnit\Framework\TestCase;

class FormBlockTest extends TestCase
{
    public function testOptionalFieldsDefaultToNull(): void
    {
        $block = new FormBlock();

        self::assertNull($block->getReturnUrl());
        self::assertNull($block->getTextAfterSubmit());
        self::assertNull($block->getLinkRelation());
    }

    public function testOptionalFieldsCanBeSetAndCleared(): void
    {
        $block = new FormBlock();

        $block->setReturnUrl('https://example.org/thanks');
        $block->setTextAfterSubmit('Thanks');

        self::assertSame('https://example.org/thanks', $block->getReturnUrl());
        self::assertSame('Thanks', $block->getTextAfterSubmit());

        $block->setReturnUrl(null);
        $block->setTextAfterSubmit(null);
        $block->setLinkRelation(null);

        self::assertNull($block->getReturnUrl());
        self::assertNull($block->getTextAfterSubmit());
        self::assertNull($block->getLinkRelation());
    }
}

