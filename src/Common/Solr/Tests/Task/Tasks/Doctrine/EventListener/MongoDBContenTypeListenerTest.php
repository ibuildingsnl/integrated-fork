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
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Task\Tasks\ContentTypeQueueTask;
use Integrated\Common\Solr\Task\Tasks\Doctrine\EventListener\MongoDBContentTypeListener;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class MongoDBContenTypeListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueueInterface&MockObject
     */
    private $queue;

    protected function setUp(): void
    {
        $this->queue = $this->createMock(QueueInterface::class);
    }

    public function testPostUpdate()
    {
        $this->queue->expects($this->once())
            ->method('push')
            ->with($this->callback(function (ContentTypeQueueTask $task) {
                self::assertEquals('this-is-the-id', $task->getId());

                return true;
            }));

        $this->getInstance()->postUpdate($this->getEvent($this->getContentType('this-is-the-id')));
    }

    public function testPostUpdateNoContentType()
    {
        $this->queue->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->postUpdate($this->getEvent(new \stdClass()));
    }

    public function testPostUpdateSkipsQueueWhenOnlyCheckboxDefaultValueChanged(): void
    {
        $this->queue->expects($this->never())
            ->method('push');

        $contentType = new ContentType();
        $contentType->setId('news');

        $before = (new Field())
            ->setName('featured')
            ->setOptions([
                'required' => false,
            ]);

        $after = (new Field())
            ->setName('featured')
            ->setOptions([
                'required' => false,
                'value' => 'checked',
            ]);

        $this->getInstance([
            'fields' => [[$before], [$after]],
        ])->postUpdate($this->getEvent($contentType));
    }

    /**
     * @return MongoDBContentTypeListener
     */
    protected function getInstance(array $changeSet = [])
    {
        return new class($this->queue, $changeSet) extends MongoDBContentTypeListener {
            public function __construct(
                QueueInterface $queue,
                private readonly array $changeSet,
            ) {
                parent::__construct($queue);
            }

            protected function getDocumentChangeSet(LifecycleEventArgs $event, ContentTypeInterface $document): array
            {
                return $this->changeSet;
            }
        };
    }

    /**
     * @param string $id
     *
     * @return ContentTypeInterface&MockObject
     */
    protected function getContentType($id)
    {
        $mock = $this->createMock(ContentTypeInterface::class);
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
