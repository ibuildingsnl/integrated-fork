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
use Integrated\Common\Locks\ResourceInterface;
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
        $captured = null;

        $request = new Request(
            new Resource('content', '42'),
            new Resource('user', '99'),
            null
        );

        $this->connection->expects($this->once())
            ->method('insert')
            ->with(
                $this->identicalTo('locks'),
                $this->callback(function (array $data) use (&$captured): bool {
                    $captured = $data;

                    return true;
                })
            );

        $lock = $this->manager->acquire($request);

        $this->assertIsArray($captured);
        $this->assertArrayHasKey('id', $captured);
        $this->assertArrayHasKey('resource', $captured);
        $this->assertArrayHasKey('resource_owner', $captured);
        $this->assertArrayHasKey('created', $captured);
        $this->assertArrayHasKey('expires', $captured);
        $this->assertArrayHasKey('timeout', $captured);
        $this->assertSame('{"type":"content","id":"42"}', $captured['resource']);
        $this->assertSame('{"type":"user","id":"99"}', $captured['resource_owner']);
        $this->assertNull($captured['timeout']);
        $this->assertNull($captured['expires']);

        $this->assertInstanceOf(LockInterface::class, $lock);
        $owner = $lock->getRequest()->getOwner();
        $this->assertInstanceOf(ResourceInterface::class, $owner);
        $this->assertSame('content', $lock->getRequest()->getResource()->getType());
        $this->assertSame('42', $lock->getRequest()->getResource()->getIdentifier());
        $this->assertSame('user', $owner->getType());
        $this->assertSame('99', $owner->getIdentifier());
        $this->assertNull($lock->getRequest()->getTimeout());
    }

    public function testAcquireDuplicateKeyReturnsNull(): void
    {
        $request = new Request(
            new Resource('content', '42'),
            new Resource('user', '99'),
            null
        );

        $this->connection->expects($this->once())
            ->method('insert')
            ->willThrowException(new \Exception('Duplicate entry'));

        $lock = $this->manager->acquire($request);

        $this->assertNull($lock);
    }
}
