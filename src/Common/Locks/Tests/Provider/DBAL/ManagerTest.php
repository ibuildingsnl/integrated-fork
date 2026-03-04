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
use Integrated\Common\Locks\LockInterface;
use Integrated\Common\Locks\Provider\DBAL\Manager;
use Integrated\Common\Locks\Provider\DBAL\Request;
use Integrated\Common\Locks\Provider\DBAL\Resource;
use PHPUnit\Framework\MockObject\MockObject;

class ManagerTest extends \PHPUnit\Framework\TestCase
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

    public function testAcquireReturnsLockForValidInsert(): void
    {
        $request = new Request(
            new Resource('content', '42'),
            new Resource('user', '99'),
            120
        );

        $this->connection->expects($this->once())
            ->method('insert')
            ->with(
                $this->identicalTo('locks'),
                $this->callback(function (array $data): bool {
                    return isset(
                        $data['id'],
                        $data['resource'],
                        $data['resource_owner'],
                        $data['created'],
                        $data['expires'],
                        $data['timeout']
                    )
                        && $data['resource'] === '{"type":"content","id":"42"}'
                        && $data['resource_owner'] === '{"type":"user","id":"99"}'
                        && $data['timeout'] === 120
                        && $data['expires'] >= $data['created'];
                })
            );

        $lock = $this->manager->acquire($request);

        $this->assertInstanceOf(LockInterface::class, $lock);
        $this->assertSame('content', $lock->getRequest()->getResource()->getType());
        $this->assertSame('42', $lock->getRequest()->getResource()->getIdentifier());
        $this->assertSame('user', $lock->getRequest()->getOwner()->getType());
        $this->assertSame('99', $lock->getRequest()->getOwner()->getIdentifier());
        $this->assertSame(120, $lock->getRequest()->getTimeout());
    }

    public function testAcquireDuplicateKeyReturnsNull(): void
    {
        $request = new Request(
            new Resource('content', '42'),
            new Resource('user', '99'),
            120
        );

        $this->connection->expects($this->once())
            ->method('insert')
            ->willThrowException(new \Exception('Duplicate entry'));

        $lock = $this->manager->acquire($request);

        $this->assertNull($lock);
    }
}
