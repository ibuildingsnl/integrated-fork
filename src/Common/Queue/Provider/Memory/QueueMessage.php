<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Queue\Provider\Memory;

use Integrated\Common\Queue\Memory\QueueMessageInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class QueueMessage implements QueueMessageInterface
{
    private $payload;

    /**
     * @var int
     */
    private $attempts;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var int
     */
    private $createdAt;

    /**
     * @var int
     */
    private $updatedAt;

    /**
     * @var int
     */
    private $executeAt;

    /**
     * @param int $attempts
     * @param int $priority
     * @param int $createdAt
     * @param int $updatedAt
     * @param int $executeAt
     */
    public function __construct($payload, $attempts, $priority, $createdAt, $updatedAt, $executeAt)
    {
        $this->payload = $payload;
        $this->attempts = $attempts;
        $this->priority = $priority;

        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->executeAt = $executeAt;
    }

    public function delete()
    {
    }

    public function release($delay = 0)
    {
    }

    public function getAttempts()
    {
        return $this->attempts;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }

    public function getExecuteAt(): int
    {
        return $this->executeAt;
    }
}
