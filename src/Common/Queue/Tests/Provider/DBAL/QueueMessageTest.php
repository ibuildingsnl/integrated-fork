<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Queue\Tests\Provider\DBAL;

use Integrated\Common\Queue\Provider\DBAL\QueueMessage;
use Integrated\Common\Queue\QueueMessageInterface;
use PHPUnit\Framework\Assert;
use stdClass;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class QueueMessageTest extends \PHPUnit\Framework\TestCase
{
    public const PAYLOAD = 'O:8:"stdClass":0:{}'; // serialized stdClass;

    protected $data;

    protected function setUp(): void
    {
        $this->data = [
            'id' => 'ThisIsTheID',
            'payload' => self::PAYLOAD,
            'attempts' => '42',
        ];
    }

    public function testInterface()
    {
        $message = new QueueMessage($this->data, function () {
        }, function () {
        });

        $this->assertInstanceOf(QueueMessageInterface::class, $message);
    }

    public function testGetPayload()
    {
        $message = new QueueMessage($this->data, function () {
        }, function () {
        });

        $this->assertInstanceOf('stdClass', $message->getPayload());
    }

    public function testGetPayloadCached()
    {
        $message = new QueueMessage($this->data, function () {
        }, function () {
        });

        $this->assertSame($message->getPayload(), $message->getPayload());
    }

    public function testGetAttempts()
    {
        $message = new QueueMessage($this->data, function () {
        }, function () {
        });

        $this->assertSame(42, $message->getAttempts());
    }

    public function testGetId()
    {
        $message = new QueueMessage($this->data, function () {
        }, function () {
        });

        $this->assertSame('ThisIsTheID', $message->getId());
    }

    public function testGetData()
    {
        $message = new QueueMessage($this->data, function () {
        }, function () {
        });

        $this->assertSame($this->data, $message->getData());
    }

    public function testRelease()
    {
        $count = 0;

        $message = new QueueMessage($this->data, function () {
            throw new \LogicException('Method was not expected to be called');
        }, function ($delay) use (&$count) {
            ++$count;

            Assert::assertEquals(0, $delay);
        });

        $message->release();
        $message->release();

        $message->delete();

        $this->assertEquals(1, $count, 'Method was not expected to be called more than once');
    }

    public function testReleaseWithDelay()
    {
        $message = new QueueMessage($this->data, function () {
        }, function ($delay) {
            Assert::assertEquals(42, $delay);
        });

        $message->release(42);
    }

    public function testDelete()
    {
        $count = 0;

        $message = new QueueMessage($this->data, function () use (&$count) {
            ++$count;
        }, function () {
            throw new \LogicException('Method was not expected to be called');
        });

        $message->delete();
        $message->release();

        $this->assertEquals(1, $count, 'Method was not expected to be called more than once');
    }
}
