<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Converter\Tests\Config;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Converter\Config\ConfigInterface;
use Integrated\Common\Converter\Config\ConfigResolverInterface;
use Integrated\Common\Converter\Tests\Config\Fixtures\TestClass;
use Integrated\Common\Converter\Tests\Config\Fixtures\TestParent;
use Integrated\Common\Converter\Tests\Config\Fixtures\TestChild;
use Integrated\Common\Converter\Exception\ExceptionInterface;
use Integrated\Common\Converter\Config\ConfigResolver;
use Integrated\Common\Converter\Config\TypeConfigInterface;
use Integrated\Common\Converter\Config\TypeProviderInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ConfigResolverTest extends TestCase
{
    protected $CONFIG_INTERFACE = ConfigInterface::class;

    /**
     * @var TypeProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(TypeProviderInterface::class);
    }

    public function testInterface()
    {
        self::assertInstanceOf(ConfigResolverInterface::class, $this->getInstance());
    }

    public function testGetConfig()
    {
        $resolver = $this->getInstance();

        $this->provider->expects($this->once())
            ->method('getTypes')
            ->with($this->equalTo(TestClass::class))
            ->willReturn([$this->getType()]);

        $config = $resolver->getConfig(TestClass::class);

        self::assertInstanceOf($this->CONFIG_INTERFACE, $config);
        self::assertFalse($config->hasParent());
        self::assertSame($config, $resolver->getConfig(TestClass::class));
    }

    public function testGetConfigParent()
    {
        $resolver = $this->getInstance();

        $this->provider->expects($this->exactly(2))
            ->method('getTypes')
            ->withConsecutive(
                [$this->equalTo(TestParent::class)],
                [$this->equalTo(TestChild::class)]
            )
            ->willReturnOnConsecutiveCalls(
                [$this->getType()],
                []
            );

        $config = $resolver->getConfig(TestChild::class);

        self::assertInstanceOf($this->CONFIG_INTERFACE, $config);
        self::assertFalse($config->hasParent());
        self::assertSame($config, $resolver->getConfig(TestParent::class));
    }

    public function testGetConfigParentAndChild()
    {
        $resolver = $this->getInstance();

        $this->provider->expects($this->exactly(2))
            ->method('getTypes')
            ->withConsecutive(
                [$this->equalTo(TestParent::class)],
                [$this->equalTo(TestChild::class)]
            )
            ->willReturnOnConsecutiveCalls(
                [$this->getType()],
                [$this->getType()]
            );

        $config = $resolver->getConfig(TestChild::class);

        self::assertInstanceOf($this->CONFIG_INTERFACE, $config);
        self::assertTrue($config->hasParent());
        self::assertSame($config->getParent(), $resolver->getConfig(TestParent::class));
    }

    public function testGetConfigNothingFound()
    {
        $this->provider->expects($this->atLeastOnce())
            ->method('getTypes')
            ->willReturn([]);

        self::assertNull($this->getInstance()->getConfig(TestChild::class));
    }

    public function testGetConfigLowerAndUpperCaseClassName()
    {
        $resolver = $this->getInstance();

        $this->provider->expects($this->once())
            ->method('getTypes')
            ->with($this->equalTo(TestClass::class))
            ->willReturn([$this->getType()]);

        self::assertSame(
            $resolver->getConfig('integrated\\common\\converter\\tests\\config\\fixtures\\testclass'),
            $resolver->getConfig('INTEGRATED\\COMMON\\CONVERTER\\TESTS\\CONFIG\\FIXTURES\\TESTCLASS')
        );
    }

    public function testGetConfigInvalidArgument()
    {
        $this->expectException(ExceptionInterface::class);

        $this->getInstance()->getConfig(42);
    }

    public function testGetConfigInvalidClass()
    {
        self::assertNull($this->getInstance()->getConfig('Integrated\\Tests\\Common\\Converter\\Config\\Fixtures\\DoesNotExist'));
    }

    /**
     * @return ConfigResolver
     */
    protected function getInstance()
    {
        return new ConfigResolver($this->provider);
    }

    /**
     * @return TypeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getType()
    {
        return $this->createMock(TypeConfigInterface::class);
    }
}
