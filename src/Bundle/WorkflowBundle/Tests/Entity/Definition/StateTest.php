<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Tests\Entity\Definition;

use PHPUnit\Framework\TestCase;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class StateTest extends TestCase
{
    /**
     * Test isDefault function.
     */
    public function testIsDefault()
    {
        // Create two states
        $state1 = $this->getInstance();
        $state2 = $this->getInstance();

        /** @var Definition|\PHPUnit_Framework_MockObject_MockObject $definition */
        $definition = $this->createMock(Definition::class);

        // Set workflow Mock for the two states
        $state1->setWorkflow($definition);
        $state2->setWorkflow($definition);

        // Stub getDefault function
        $definition
            ->expects($this->exactly(2))
            ->method('getDefault')
            ->willReturn($state1)
        ;

        // Asserts
        $this->assertTrue($state1->isDefault());
        $this->assertFalse($state2->isDefault());
    }

    /**
     * @return State
     */
    protected function getInstance()
    {
        return new State();
    }
}
