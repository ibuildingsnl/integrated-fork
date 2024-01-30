<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Queue\Tests\Provider\Memory;

use Integrated\Common\Queue\Provider\Memory\QueueMessage;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class QueueMessageTest extends \PHPUnit\Framework\TestCase
{
    public function testInterface()
    {
        $message = new Queuecd Message(null, 0, 0, 0, 0, 0, function () {
        });

        $this->assertInstanceOf('Integrated\Common\Queue\QueueMessageInterface', $message);
    }

    public function testGetPayload()
    {
        $payload = new \stdClass();
        $message = new QueueMessage($payload, 0, 0, 0, 0, 0, function () {
        });

        $this->assertSame($message->getPayload(), $payload);
    }

    public function testGetAttempts()
    {
        $message = new QueueMessage(null, 42, 0, 0, 0, 0, function () {
        });

        $this->assertEquals(42, $message->getAttempts());
    }

    public function testGetPriority()
    {
        $message = new QueueMessage(null, 0, 10, 0, 0, 0, function () {
        });

        $this->assertEquals(10, $message->getPriority());
    }

    public function testGetCreatedAt()
    {
        $createdAt = 1234;
        $message = new QueueMessage(null, 0, 0, $createdAt, 0, 0, function () {
        });

        $this->assertEquals($createdAt, $message->getCreatedAt());
    }

    public function testGetUpdatedAt()
    {
        $updatedAt = 5678;
        $message = new QueueMessage(null, 0, 0, 0, $updatedAt, 0, function () {
        });

        $this->assertEquals($updatedAt, $message->getUpdatedAt());
    }

    public function testGetExecuteAt()
    {
        $executeAt = 9642;
        $message = new QueueMessage(null, 0, 0, 0, 0, $executeAt, function () {
        });

        $this->assertEquals($executeAt, $message->getExecuteAt());
    }
}
