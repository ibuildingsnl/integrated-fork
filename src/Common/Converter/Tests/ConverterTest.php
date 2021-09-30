<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Converter\Tests;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Converter\ConverterInterface;
use stdClass;
use Integrated\Common\Converter\Type\ResolvedTypeInterface;
use Integrated\Common\Converter\Exception\ExceptionInterface;
use Integrated\Common\Converter\Exception\RuntimeException;
use Integrated\Common\Converter\Config\ConfigInterface;
use Integrated\Common\Converter\Config\ConfigResolverInterface;
use Integrated\Common\Converter\Config\TypeConfigInterface;
use Integrated\Common\Converter\ContainerFactoryInterface;
use Integrated\Common\Converter\ContainerInterface;
use Integrated\Common\Converter\Converter;
use Integrated\Common\Converter\Type\RegistryInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ConverterTest extends TestCase
{
    /**
     * @var RegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var ConfigResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolver;

    /**
     * @var ContainerFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->resolver = $this->createMock(ConfigResolverInterface::class);
        $this->factory = $this->createMock(ContainerFactoryInterface::class);
    }

    public function testInterface()
    {
        self::assertInstanceOf(ConverterInterface::class, $this->getInstance());
    }

    public function testConvert()
    {
        $container = $this->getContainer();

        $this->factory->expects($this->once())
            ->method('createContainer')
            ->willReturn($container);

        $this->resolver->expects($this->once())
            ->method('getConfig')
            ->with($this->equalTo(stdClass::class))
            ->willReturn($this->getConfig([$this->getType('type-1', null), $this->getType('type-2', ['options'])]));

        $data = new stdClass();

        $type1 = $this->createMock(ResolvedTypeInterface::class);
        $type1->expects($this->once())
            ->method('build')
            ->with($this->identicalTo($container), $this->identicalTo($data), $this->equalTo([]));

        $type2 = $this->createMock(ResolvedTypeInterface::class);
        $type2->expects($this->once())
            ->method('build')
            ->with($this->identicalTo($container), $this->identicalTo($data), $this->equalTo(['options']));

        $this->registry->expects($this->exactly(2))
            ->method('getType')
            ->willReturnMap([
                ['type-1', $type1],
                ['type-2', $type2],
            ]);

        self::assertSame($container, $this->getInstance()->convert($data));
    }

    public function testConvertNoConfigFound()
    {
        $container = $this->getContainer();

        $this->factory->expects($this->once())
            ->method('createContainer')
            ->willReturn($container);

        $this->resolver->expects($this->once())
            ->method('getConfig')
            ->with($this->equalTo(stdClass::class))
            ->willReturn(null);

        $this->registry->expects($this->never())
            ->method($this->anything());

        self::assertSame($container, $this->getInstance()->convert(new stdClass()));
    }

    public function testConvertTypeNotFound()
    {
        $this->expectException(ExceptionInterface::class);

        $this->factory->expects($this->once())
            ->method('createContainer')
            ->willReturn($this->getContainer());

        $this->resolver->expects($this->once())
            ->method('getConfig')
            ->with($this->equalTo(stdClass::class))
            ->willReturn($this->getConfig([$this->getType('does-not-exist')]));

        $this->registry->expects($this->any())
            ->method('getType')
            ->with($this->equalTo('does-not-exist'))
            ->willThrowException($this->createMock(RuntimeException::class));

        $this->getInstance()->convert(new stdClass());
    }

    public function testConvertInvalidArgument()
    {
        $this->expectException(ExceptionInterface::class);

        $this->factory->expects($this->never())
            ->method('createContainer');

        $this->getInstance()->convert(42);
    }

    public function testConvertNullArgument()
    {
        $container = $this->getContainer();

        $this->factory->expects($this->once())
            ->method('createContainer')
            ->willReturn($container);

        self::assertSame($container, $this->getInstance()->convert(null));
    }

    /**
     * @return Converter
     */
    protected function getInstance()
    {
        return new Converter($this->registry, $this->resolver, $this->factory);
    }

    /**
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainer()
    {
        $mock = $this->createMock(ContainerInterface::class);
        $mock->expects($this->never())
            ->method($this->anything()); // the convert self should not nothing with the container

        return $mock;
    }

    /**
     * @param TypeConfigInterface[] $types
     *
     * @return ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfig(array $types)
    {
        $mock = $this->createMock(ConfigInterface::class);

        $mock->expects($this->any())
            ->method('hasParent')
            ->willReturn(false);

        $mock->expects($this->any())
            ->method('getParent')
            ->willReturn(null);

        $mock->expects($this->any())
            ->method('getTypes')
            ->willReturn($types);

        return $mock;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return TypeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getType($name, array $options = null)
    {
        $mock = $this->createMock(TypeConfigInterface::class);

        $mock->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        $mock->expects($this->any())
            ->method('hasOptions')
            ->willReturn($options !== null);

        $mock->expects($this->any())
            ->method('getOptions')
            ->willReturn($options);

        return $mock;
    }
}
