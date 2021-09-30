<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Converter\Tests\Type;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Converter\Type\RegistryBuilderInterface;
use Integrated\Common\Converter\Type\ResolvedTypeInterface;
use Integrated\Common\Converter\Type\Registry;
use Integrated\Common\Converter\Type\RegistryBuilder;
use Integrated\Common\Converter\Type\ResolvedTypeFactoryInterface;
use Integrated\Common\Converter\Type\TypeExtensionInterface;
use Integrated\Common\Converter\Type\TypeInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RegistryBuilderTest extends TestCase
{
    /**
     * @var ResolvedTypeFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(ResolvedTypeFactoryInterface::class);
    }

    public function testInterface()
    {
        self::assertInstanceOf(RegistryBuilderInterface::class, $this->getInstance());
    }

    public function testAddType()
    {
        $type = $this->getType('test');

        $builder = $this->getInstance();
        $builder->addType($type);

        $resolved = $this->createMock(ResolvedTypeInterface::class);

        $this->factory->expects($this->once())
            ->method('createType')
            ->with($this->equalTo($type), $this->equalTo([]))
            ->willReturn($resolved);

        self::assertSame($resolved, $builder->getRegistry()->getType('test'));
    }

    public function testAddTypes()
    {
        $type1 = $this->getType('test1');
        $type2 = $this->getType('test2');
        $type3 = $this->getType('test1');

        $builder = $this->getInstance();
        $builder->addTypes([$type1, $type2, $type3]);

        $resolved = $this->createMock(ResolvedTypeInterface::class);

        $this->factory->expects($this->exactly(2))
            ->method('createType')
            ->withConsecutive(
                [$this->equalTo($type3), $this->equalTo([])],
                [$this->equalTo($type2), $this->equalTo([])]
            )
            ->willReturn($resolved);

        $registry = $builder->getRegistry();

        self::assertSame($resolved, $registry->getType('test1'));
        self::assertSame($resolved, $registry->getType('test2'));
    }

    public function testAddTypeExtension()
    {
        $extension = $this->getTypeExtension('test');
        $type = $this->getType('test');

        $builder = $this->getInstance();
        $builder->addTypeExtension($extension);
        $builder->addType($type);

        $resolved = $this->createMock(ResolvedTypeInterface::class);

        $this->factory->expects($this->once())
            ->method('createType')
            ->with($this->equalTo($type), $this->equalTo([$extension]))
            ->willReturn($resolved);

        self::assertSame($resolved, $builder->getRegistry()->getType('test'));
    }

    public function testAddTypeExtensionNoType()
    {
        $builder = $this->getInstance();
        $builder->addTypeExtension($this->getTypeExtension('test'));

        $this->factory->expects($this->never())
            ->method('createType');
    }

    public function testAddTypeExtensions()
    {
        $extension1 = $this->getTypeExtension('test');
        $extension2 = $this->getTypeExtension('test');

        $type = $this->getType('test');

        $builder = $this->getInstance();
        $builder->addTypeExtensions([$extension1, $extension2]);
        $builder->addType($type);

        $resolved = $this->createMock(ResolvedTypeInterface::class);

        $this->factory->expects($this->once())
            ->method('createType')
            ->with($this->equalTo($type), $this->equalTo([$extension1, $extension2]))
            ->willReturn($resolved);

        self::assertSame($resolved, $builder->getRegistry()->getType('test'));
    }

    public function testNoSetResolvedTypeFactory()
    {
        $builder = $this->getInstance(false);
        $builder->addType($this->getType('test'));

        self::assertInstanceOf(Registry::class, $builder->getRegistry());
    }

    /**
     * @return RegistryBuilder
     */
    protected function getInstance($factory = true)
    {
        $instance = new RegistryBuilder();

        if ($factory) {
            $instance->setResolvedTypeFactory($this->factory);
        }

        return $instance;
    }

    /**
     * @param string $name
     *
     * @return TypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getType($name)
    {
        $mock = $this->createMock(TypeInterface::class);
        $mock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn($name);

        return $mock;
    }

    /**
     * @param string $name
     *
     * @return TypeExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeExtension($name)
    {
        $mock = $this->createMock(TypeExtensionInterface::class);
        $mock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn($name);

        return $mock;
    }
}
