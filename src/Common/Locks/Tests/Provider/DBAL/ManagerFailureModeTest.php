<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Locks\Tests\Provider\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Integrated\Common\Locks\Filter;
use Integrated\Common\Locks\Provider\DBAL\Manager;
use PHPUnit\Framework\MockObject\MockObject;

class ManagerFailureModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Connection&MockObject
     */
    private $connection;

    /**
     * @var Manager
     */
    private $manager;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connection->method('getDatabasePlatform')
            ->willReturn(new MySQLPlatform());

        $this->manager = new Manager($this->connection, [
            'lock_table_name' => 'locks',
        ]);
    }

    public function testReleaseDbFailureThrowsRuntimeException(): void
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with('locks', ['id' => 'lock-1'])
            ->willThrowException(new \Exception('DB write failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to release lock lock-1');

        $this->manager->release('lock-1');
    }

    public function testFindByDbFailureThrowsRuntimeException(): void
    {
        $filter = new Filter();
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('l.*')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('locks', 'l')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getSQL')
            ->willReturn('SELECT l.* FROM locks l');

        $queryBuilder->expects($this->once())
            ->method('getParameters')
            ->willReturn([]);

        $this->connection->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $this->connection->expects($this->once())
            ->method('fetchAllAssociative')
            ->willThrowException(new \Exception('DB read failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to find locks by filter');

        $this->manager->findBy($filter);
    }
}
