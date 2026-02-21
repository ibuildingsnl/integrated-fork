<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Tests\Task\Event;

use Integrated\Common\Solr\Task\Event\WorkerEvent;
use Integrated\Common\Solr\Task\Worker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class WorkerEventTest extends TestCase
{
    /**
     * @var Worker&MockObject
     */
    protected $worker;

    protected function setUp(): void
    {
        $this->worker = $this->getMockBuilder(Worker::class)->disableOriginalConstructor()->getMock();
    }

    public function testParent()
    {
        self::assertInstanceOf(Event::class, $this->getInstance());
    }

    public function testGetIndexer()
    {
        self::assertSame($this->worker, $this->getInstance()->getWorker());
    }

    protected function getInstance()
    {
        return new WorkerEvent($this->worker);
    }
}
