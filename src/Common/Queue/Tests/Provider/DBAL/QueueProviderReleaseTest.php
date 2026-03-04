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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Integrated\Common\Queue\Provider\DBAL\QueueProvider;
use PHPUnit\Framework\MockObject\MockObject;

class QueueProviderReleaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Connection&MockObject
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connection->method('getDatabasePlatform')
            ->willReturn(new MySQLPlatform());
    }

    public function testReleaseReschedulesMessageWithDelay(): void
    {
        $this->connection->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn([
                [
                    'id' => 'message-id-1',
                    'payload' => 's:4:"test";',
                    'attempts' => 2,
                    'priority' => 0,
                ],
            ]);

        $this->connection->expects($this->once())
            ->method('executeStatement')
            ->with(
                $this->stringContains('SET attempts = attempts + 1'),
                $this->callback(function (array $params): bool {
                    return $params['id'] === 'message-id-1'
                        && isset($params['updated'], $params['execute'])
                        && $params['execute'] >= $params['updated'] + 30;
                })
            );

        $provider = new QueueProvider($this->connection, [
            'queue_table_name' => 'queue',
        ]);

        $messages = $provider->pull('solr-indexer', 1);

        self::assertCount(1, $messages);

        $messages[0]->release(30);
    }
}
