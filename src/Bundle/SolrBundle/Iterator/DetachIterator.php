<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SolrBundle\Iterator;

use Doctrine\Persistence\ObjectManager;

/**
 * @author Patrick Mestebeld <patrick@e-active.nl>
 */
class DetachIterator implements \Iterator
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var \Iterator
     */
    private $iterator;

    public function __construct(\Iterator $iterator, ObjectManager $manager)
    {
        $this->iterator = $iterator;
        $this->manager = $manager;
    }

    public function current(): mixed
    {
        $this->manager->detach($current = $this->iterator->current());

        return $current;
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key(): mixed
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}
