<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Tests\Form\EventListener;

use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;
use Integrated\Bundle\WorkflowBundle\Form\EventListener\WorkflowStateListener;
use PHPUnit\Framework\TestCase;

class WorkflowStateListenerTest extends TestCase
{
    public function testGetChoicesFiltersCurrentStateAndDuplicateTransitionIds(): void
    {
        $current = (new State())->setName('Current');
        $other = (new State())->setName('Other');
        $otherDuplicate = (new State())->setName('Other duplicate');
        $sameAsCurrent = (new State())->setName('Current duplicate');

        $this->setStateId($otherDuplicate, $other->getId());
        $this->setStateId($sameAsCurrent, $current->getId());

        $current->addTransition($sameAsCurrent);
        $current->addTransition($other);
        $current->addTransition($otherDuplicate);

        $listener = new class($this->createMock(Definition::class)) extends WorkflowStateListener {
            /** @return array<int|string, State> */
            public function exposeChoices(State $state): array
            {
                return $this->getChoices($state);
            }
        };

        $choices = $listener->exposeChoices($current);

        self::assertCount(1, $choices);
        self::assertSame($other->getId(), $choices[0]->getId());
    }

    private function setStateId(State $state, string $id): void
    {
        $reflection = new \ReflectionObject($state);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($state, $id);
    }
}
