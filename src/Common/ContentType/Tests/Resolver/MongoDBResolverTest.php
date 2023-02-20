<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\ContentType\Tests\Resolver;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\Resolver\MongoDBResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class MongoDBResolverTest extends TestCase
{
    /**
     * @var DocumentRepository|MockObject
     */
    private $repository;

    protected function setUp(): void
    {
        $class = ContentTypeInterface::class;

        $this->repository = $this->getMockBuilder('Doctrine\\ODM\\MongoDB\\Repository\\DocumentRepository')->disableOriginalConstructor()->getMock();
        $this->repository->expects($this->any())
            ->method('getClassName')
            ->willReturn($class);
    }

    public function testInterface()
    {
        self::assertInstanceOf('Integrated\\Common\\ContentType\\ResolverInterface', $this->getInstance());
    }

    public function testInvalidRepository()
    {
        $this->expectException(\Integrated\Common\ContentType\Exception\ExceptionInterface::class);

        $this->repository = $this->getMockBuilder('Doctrine\\ODM\\MongoDB\\Repository\\DocumentRepository')->disableOriginalConstructor()->getMock();
        $this->repository->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');

        $this->getInstance();
    }

    public function testGetType()
    {
        $type = $this->getType();

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($type);

        $resolver = $this->getInstance();

        self::assertSame($type, $resolver->getType('found'));
        self::assertSame($type, $resolver->getType('found')); // second call should return cached version
    }

    public function testGetTypeNoString()
    {
        $this->expectException(\Integrated\Common\ContentType\Exception\UnexpectedTypeException::class);

        $this->getInstance()->getType(['not a string']);
    }

    public function testGetTypeNotFound()
    {
        $this->expectException(\Integrated\Common\ContentType\Exception\ExceptionInterface::class);
        $this->expectExceptionMessage('"not found"');

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->getInstance()->getType('not found');
    }

    public function testHasType()
    {
        $this->repository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnMap([
                [['id' => 'found'], null, $this->getType()],
                [['id' => 'not found'], null, null],
            ]);

        $resolver = $this->getInstance();

        self::assertTrue($resolver->hasType('found'));
        self::assertTrue($resolver->hasType('found')); // second call should use cached version
        self::assertFalse($resolver->hasType('not found'));
    }

    public function testHasTypeNoString()
    {
        $this->expectException(\Integrated\Common\ContentType\Exception\UnexpectedTypeException::class);

        $this->getInstance()->hasType(['not a string']);
    }

    public function testGetTypes()
    {
        $types = [
            $this->getType('type 1'),
            $this->getType('type 2'),
        ];

        $this->repository->expects($this->once())
            ->method('findBy')
            ->willReturn($types);

        $iterator = $this->getInstance()->getTypes();

        self::assertInstanceOf('Integrated\\Common\\ContentType\\IteratorInterface', $iterator);
        self::assertSame(['type 1' => $types[0], 'type 2' => $types[1]], iterator_to_array($iterator));
    }

    /**
     * @return MongoDBResolver
     */
    protected function getInstance()
    {
        return new MongoDBResolver($this->repository);
    }

    /**
     * @return ContentTypeInterface|MockObject
     */
    protected function getType($name = null)
    {
        $mock = $this->createMock('Integrated\\Common\\ContentType\\ContentTypeInterface');

        if ($name !== null) {
            $mock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn($name);
        }

        return $mock;
    }
}
