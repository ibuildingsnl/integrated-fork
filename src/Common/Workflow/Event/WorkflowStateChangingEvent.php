<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Workflow\Event;

use Integrated\Bundle\WorkflowBundle\Entity\Definition\State as DefinitionState;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State as WorkflowState;
use Integrated\Common\Content\ContentInterface;
use Symfony\Contracts\EventDispatcher\Event;

class WorkflowStateChangingEvent extends Event
{
    private bool $denied = false;

    private ?string $message = null;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly ContentInterface $content,
        private readonly WorkflowState $workflowState,
        private readonly DefinitionState $targetState,
        private readonly array $data = [],
    ) {
    }

    public function getContent(): ContentInterface
    {
        return $this->content;
    }

    public function getWorkflowState(): WorkflowState
    {
        return $this->workflowState;
    }

    public function getCurrentState(): DefinitionState
    {
        return $this->workflowState->getState();
    }

    public function getTargetState(): DefinitionState
    {
        return $this->targetState;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function deny(?string $message = null): void
    {
        $this->denied = true;
        $this->message = $message;
    }

    public function isDenied(): bool
    {
        return $this->denied;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
