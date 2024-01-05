<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Tests\Indexer\Event;

use Integrated\Common\Solr\Indexer\Event\IndexerEvent;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class IndexerEventTest extends TestCase
{
    /**
     * @var IndexerInterface|MockObject
     */
    protected $indexer;

    protected function setUp(): void
    {
        $this->indexer = $this->createMock(IndexerInterface::class);
    }

    public function testParent()
    {
        self::assertInstanceOf(Event::class, $this->getInstance());
    }

    public function testGetIndexer()
    {
        self::assertSame($this->indexer, $this->getInstance()->getIndexer());
    }

    protected function getInstance()
    {
        return new IndexerEvent($this->indexer);
    }
}
