<?php

namespace Integrated\Common\Content\Form\Event;

use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Symfony\Contracts\EventDispatcher\Event;

class BlockEvent extends Event
{
    private Block $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function getBlock(): Block
    {
        return $this->block;
    }
}
