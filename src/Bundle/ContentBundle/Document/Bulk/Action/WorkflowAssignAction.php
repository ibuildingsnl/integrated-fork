<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Bulk\Action;

use Integrated\Common\Bulk\BulkActionInterface;

class WorkflowAssignAction implements BulkActionInterface
{
    /**
     * @var string
     */
    private $handler;

    /**
     * @var string|null
     */
    private $assigned;

    public function __construct(string $handler)
    {
        $this->handler = $handler;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    public function getAssigned(): ?string
    {
        return $this->assigned;
    }

    public function setAssigned(?string $assigned)
    {
        $this->assigned = $assigned;

        return $this;
    }

    public function getOptions()
    {
        return [
            'assigned' => $this->getAssigned(),
        ];
    }
}

