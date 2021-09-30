<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\ContentType\Tests\Form\Custom\Type;

use PHPUnit\Framework\TestCase;
use Integrated\Common\ContentType\Form\Custom\Type\RegistryInterface;
use ArrayIterator;
use Integrated\Common\ContentType\Form\Custom\TypeInterface;
use Integrated\Common\ContentType\Form\Custom\Type\Registry;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class RegistryTest extends TestCase
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->registry = new Registry();
    }

    /**
     * Test interface of typeRegistry.
     */
    public function testInterface()
    {
        $this->assertInstanceOf(RegistryInterface::class, $this->registry);
    }

    /**
     * Test getIterator function.
     */
    public function testGetIterator()
    {
        $this->assertInstanceOf(ArrayIterator::class, $this->registry->getIterator());
    }

    /**
     * Test add function.
     */
    public function testAddFunction()
    {
        /** @var TypeInterface|\PHPUnit_Framework_MockObject_MockObject $mock1 */
        $mock1 = $this->createMock(TypeInterface::class);

        /** @var TypeInterface|\PHPUnit_Framework_MockObject_MockObject $mock2 */
        $mock2 = $this->createMock(TypeInterface::class);

        // Add mock1 two times and mock2 one time
        $this->assertSame($this->registry, $this->registry->add($mock1));
        $this->assertSame($this->registry, $this->registry->add($mock1));
        $this->assertSame($this->registry, $this->registry->add($mock2));

        // There should be only two items in the iterator
        $this->assertCount(2, $this->registry->getIterator());
    }

    /**
     * Test has function.
     */
    public function testHasFunction()
    {
        /** @var TypeInterface|\PHPUnit_Framework_MockObject_MockObject $mock1 */
        $mock1 = $this->createMock(TypeInterface::class);

        /** @var TypeInterface|\PHPUnit_Framework_MockObject_MockObject $mock2 */
        $mock2 = $this->createMock(TypeInterface::class);

        // Add mock1
        $this->assertSame($this->registry, $this->registry->add($mock1));

        // Assert has function
        $this->assertTrue($this->registry->has($mock1));
        $this->assertFalse($this->registry->has($mock2));
    }
}
