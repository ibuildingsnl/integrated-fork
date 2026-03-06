<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Tests\Exporter;

use Integrated\Common\Channel\Connector\ExporterInterface;
use Integrated\Common\Channel\Exporter\Queue\RequestSerializerInterface;
use Integrated\Common\Channel\Exporter\QueueExporter;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Queue\QueueMessageInterface;
use PHPUnit\Framework\MockObject\MockObject;

class QueueExporterRetryPolicyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueueInterface&MockObject
     */
    private $queue;

    /**
     * @var RequestSerializerInterface&MockObject
     */
    private $serializer;

    /**
     * @var ExporterInterface&MockObject
     */
    private $exporter;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(QueueInterface::class);
        $this->serializer = $this->createMock(RequestSerializerInterface::class);
        $this->exporter = $this->createMock(ExporterInterface::class);
    }

    public function testTransientFailureReleasesMessageWithoutThrowing(): void
    {
        $message = $this->createMock(QueueMessageInterface::class);
        $message->method('getAttempts')
            ->willReturn(1);
        $message->expects($this->once())
            ->method('release')
            ->with($this->identicalTo(300));
        $message->expects($this->never())
            ->method('delete');

        $this->queue->expects($this->once())
            ->method('pull')
            ->with($this->identicalTo(1000))
            ->willReturn([$message]);

        $this->queue->expects($this->never())
            ->method('push');

        $instance = $this->getMockBuilder(QueueExporter::class)
            ->setConstructorArgs([$this->queue, $this->serializer, $this->exporter, 5])
            ->onlyMethods(['process'])
            ->getMock();

        $instance->expects($this->once())
            ->method('process')
            ->willThrowException(new \RuntimeException('Temporary failure'));

        self::assertSame(0, $instance->exportMessages());
    }

    public function testMaxAttemptsDeletesAndThrows(): void
    {
        $message = $this->createMock(QueueMessageInterface::class);
        $message->method('getAttempts')
            ->willReturn(5);
        $message->expects($this->never())
            ->method('release');
        $message->expects($this->once())
            ->method('delete');

        $this->queue->expects($this->once())
            ->method('pull')
            ->with($this->identicalTo(1000))
            ->willReturn([$message]);

        $instance = $this->getMockBuilder(QueueExporter::class)
            ->setConstructorArgs([$this->queue, $this->serializer, $this->exporter, 5])
            ->onlyMethods(['process'])
            ->getMock();

        $instance->expects($this->once())
            ->method('process')
            ->willThrowException(new \RuntimeException('Permanent failure'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Permanent failure');
        $instance->exportMessages();
    }
}
