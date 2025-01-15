<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Tests\Task\Tasks\Doctrine\EventListener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Task\Tasks\Doctrine\EventListener\MongoDBReferencesListener;
use Integrated\Common\Solr\Task\Tasks\Doctrine\MongoDBReferenceQueueTask;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class MongoDBReferencesListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueueInterface&MockObject
     */
    private $queue;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(QueueInterface::class);
    }

    public function testPostPersist()
    {
        $this->queue->expects($this->once())
            ->method('push')
            ->with($this->callback(function (MongoDBReferenceQueueTask $task) {
                self::assertEquals('this-is-the-id', $task->getId());

                return true;
            }));

        $this->getInstance()->postPersist($this->getEvent($this->getContent('this-is-the-id')));
    }

    public function testPostPersistNoContent()
    {
        $this->queue->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->postPersist($this->getEvent(new \stdClass()));
    }

    public function testPostUpdate()
    {
        $this->queue->expects($this->once())
            ->method('push')
            ->with($this->callback(function (MongoDBReferenceQueueTask $task) {
                self::assertEquals('this-is-the-id', $task->getId());

                return true;
            }));

        $this->getInstance()->postUpdate($this->getEvent($this->getContent('this-is-the-id')));
    }

    public function testPostUpdateNoContent()
    {
        $this->queue->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->postUpdate($this->getEvent(new \stdClass()));
    }

    /**
     * @return MongoDBReferencesListener
     */
    protected function getInstance()
    {
        return new MongoDBReferencesListener($this->queue);
    }

    /**
     * @param string $id
     *
     * @return ContentInterface&MockObject
     */
    protected function getContent($id)
    {
        $mock = $this->createMock(ContentInterface::class);
        $mock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);

        return $mock;
    }

    /**
     * @param object $document
     *
     * @return LifecycleEventArgs&MockObject
     */
    protected function getEvent($document)
    {
        $mock = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->atLeastOnce())
            ->method('getDocument')
            ->willReturn($document);

        return $mock;
    }
}
