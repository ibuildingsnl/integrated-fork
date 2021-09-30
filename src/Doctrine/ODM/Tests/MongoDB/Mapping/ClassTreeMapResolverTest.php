<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Doctrine\ODM\Tests\MongoDB\Mapping;

use PHPUnit\Framework\TestCase;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestClass;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestChild4;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestChild3;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestChild2;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestChild1;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestRoot2;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestRoot1;
use Integrated\Doctrine\ODM\Tests\MongoDB\Mapping\Fixtures\TestBase;
use Integrated\Doctrine\ODM\MongoDB\Mapping\ClassTreeMapResolver;
use Integrated\Doctrine\ODM\MongoDB\Mapping\Locator\ClassLocatorInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ClassTreeMapResolverTest extends TestCase
{
    /**
     * @var ClassLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $locator;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(ClassLocatorInterface::class);
    }

    protected function setUpLocator()
    {
        $this->locator->expects($this->once())
            ->method('getClassNames')
            ->willReturn([
                TestClass::class,
                TestChild4::class,
                TestChild3::class,
                TestChild2::class,
                TestChild1::class,
                TestRoot2::class.
                TestRoot1::class,
                TestBase::class,
            ]);
    }

    public function testResolveRoot()
    {
        $this->setUpLocator();

        $expected = [
            TestChild1::class => TestChild1::class,
        ];

        self::assertEquals($expected, $this->getInstance()->resolve(TestRoot1::class));
    }

    public function testResolveChild()
    {
        $this->setUpLocator();

        $expected = [
            TestChild4::class => TestChild4::class,
            TestChild3::class => TestChild3::class,
            TestChild2::class => TestChild2::class,
        ];

        $resolver = $this->getInstance();

        self::assertEquals($expected, $resolver->resolve(TestChild4::class));
        self::assertEquals($expected, $resolver->resolve(TestChild3::class));
        self::assertEquals($expected, $resolver->resolve(TestChild2::class));
    }

    public function testResolveNotInRoot()
    {
        $this->locator->expects($this->never())
            ->method($this->anything());

        $resolver = $this->getInstance();

        self::assertNull($resolver->resolve(TestBase::class));
        self::assertNull($resolver->resolve(TestClass::class));
    }

    /**
     * @return ClassTreeMapResolver
     */
    protected function getInstance()
    {
        return new ClassTreeMapResolver($this->locator, [TestRoot1::class, TestRoot2::class]);
    }
}
