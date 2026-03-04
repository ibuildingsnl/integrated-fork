<?php

namespace Integrated\Common\Services\Tests;

use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;
use Integrated\Common\Services\Flusher;
use Integrated\Common\Services\LockingFlusher;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

class LockingFlusherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LockFactory&MockObject
     */
    private $lockFactory;

    /**
     * @var Flusher&MockObject
     */
    private $next;

    /**
     * @var SharedLockInterface&MockObject
     */
    private $lock;

    protected function setUp(): void
    {
        $this->lockFactory = $this->createMock(LockFactory::class);
        $this->next = $this->createMock(Flusher::class);
        $this->lock = $this->createMock(SharedLockInterface::class);

        $this->lockFactory->expects($this->once())
            ->method('createLock')
            ->with(ContentController::class)
            ->willReturn($this->lock);
    }

    public function testFlushAcquiresDelegatesAndReleases(): void
    {
        $this->lock->expects($this->once())
            ->method('acquire')
            ->with(true)
            ->willReturn(true);

        $this->next->expects($this->once())
            ->method('flush');

        $this->lock->expects($this->once())
            ->method('release');

        $flusher = new LockingFlusher($this->lockFactory, $this->next);
        $flusher->flush();
    }

    public function testFlushWrapsFailureAndStillReleasesLock(): void
    {
        $this->lock->expects($this->once())
            ->method('acquire')
            ->with(true)
            ->willReturn(true);

        $this->next->expects($this->once())
            ->method('flush')
            ->willThrowException(new \RuntimeException('next flusher failed'));

        $this->lock->expects($this->once())
            ->method('release');

        $flusher = new LockingFlusher($this->lockFactory, $this->next);

        try {
            $flusher->flush();
            $this->fail('Expected FlushingException was not thrown');
        } catch (FlushingException $exception) {
            $this->assertStringContainsString('next flusher failed', $exception->getMessage());
            $this->assertInstanceOf(\RuntimeException::class, $exception->getPrevious());
        }
    }
}
