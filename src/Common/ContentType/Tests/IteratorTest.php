<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\ContentType\Tests;

use PHPUnit\Framework\TestCase;
use Integrated\Common\ContentType\IteratorInterface;
use ArrayIterator;
use Integrated\Common\ContentType\Iterator;

/**
 * The iterator is nothing more then a array iterator that implements the IteratorInterface
 * interface to give some extra code completion. So just check for that and don't write test
 * to test the spl array iterator implementation.
 *
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class IteratorTest extends TestCase
{
    public function testInterface()
    {
        self::assertInstanceOf(IteratorInterface::class, $this->getInstance());
        self::assertInstanceOf(ArrayIterator::class, $this->getInstance());
    }

    /**
     * @return Iterator
     */
    protected function getInstance()
    {
        return new Iterator();
    }
}
