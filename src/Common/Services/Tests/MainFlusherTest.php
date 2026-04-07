<?php

namespace Integrated\Common\Services\Tests;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Services\MainFlusher;
use Integrated\Common\Solr\Configurable;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\Common\Solr\Indexer\Job;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use Solarium\Core\Client\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MainFlusherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DocumentManager&MockObject
     */
    private $doctrine;

    /**
     * @var QueueSubscriber&MockObject
     */
    private $queueSubscriber;

    /**
     * @var QueueInterface&MockObject
     */
    private $queue;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(DocumentManager::class);
        $this->queueSubscriber = $this->createMock(QueueSubscriber::class);
        $this->queue = $this->createMock(QueueInterface::class);
    }

    public function testFlushSetsHighPriorityAndCommitsOnce(): void
    {
        $indexer = new class extends Configurable implements IndexerInterface {
            public int $executeCalls = 0;

            protected function configureOptions(OptionsResolver $resolver): void
            {
                $resolver->setDefaults(['queue.size' => 5000]);
            }

            /**
             * @return void
             */
            public function setClient(Client $client)
            {
            }

            /**
             * @return void
             */
            public function setQueue(QueueInterface $queue)
            {
            }

            /**
             * @return void
             */
            public function execute(?Client $client = null)
            {
                ++$this->executeCalls;
            }
        };

        $this->queueSubscriber->expects($this->once())
            ->method('setPriority')
            ->with(QueueInterface::PRIORITY_HIGH);

        $this->doctrine->expects($this->once())
            ->method('flush');

        $this->queue->expects($this->once())
            ->method('push')
            ->with(
                $this->callback(function ($payload): bool {
                    return $payload instanceof Job
                        && $payload->getAction() === 'COMMIT'
                        && $payload->getOption('softcommit') === 'true';
                }),
                0,
                -10
            );

        $flusher = new MainFlusher($this->doctrine, $this->queueSubscriber, $indexer, $this->queue);
        $flusher->flush();

        $this->assertSame(5000, $indexer->getOption('queue.size'));
        $this->assertSame(1, $indexer->executeCalls);
    }

    public function testFlushWrapsIndexerFailureInFlushingException(): void
    {
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('indexer failed'));

        $this->queueSubscriber->expects($this->once())
            ->method('setPriority')
            ->with(QueueInterface::PRIORITY_HIGH);

        $this->doctrine->expects($this->once())
            ->method('flush');

        $this->queue->expects($this->once())
            ->method('push');

        $flusher = new MainFlusher($this->doctrine, $this->queueSubscriber, $indexer, $this->queue);

        try {
            $flusher->flush();
            $this->fail('Expected FlushingException was not thrown');
        } catch (FlushingException $exception) {
            $this->assertStringContainsString('indexer failed', $exception->getMessage());
            $this->assertInstanceOf(\RuntimeException::class, $exception->getPrevious());
        }
    }
}
