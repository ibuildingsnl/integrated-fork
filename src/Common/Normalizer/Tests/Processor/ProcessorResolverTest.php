<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Normalizer\Tests\Processor;

use Integrated\Common\Normalizer\Processor\ProcessorInterface;
use Integrated\Common\Normalizer\Processor\ProcessorResolver;
use Integrated\Common\Normalizer\Processor\RegistryInterface;
use Integrated\Common\Normalizer\Processor\ResolvedProcessorFactoryInterface;
use Integrated\Common\Normalizer\Processor\ResolvedProcessorInterface;
use Integrated\Common\Normalizer\Processor\ResolverInterface;
use Integrated\Common\Normalizer\Tests\Fixtures\TestChild;
use Integrated\Common\Normalizer\Tests\Fixtures\TestClass;
use Integrated\Common\Normalizer\Tests\Fixtures\TestParent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ProcessorResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RegistryInterface&MockObject
     */
    private $registry;

    /**
     * @var ResolvedProcessorFactoryInterface&MockObject
     */
    private $factory;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->factory = $this->createMock(ResolvedProcessorFactoryInterface::class);
    }

    public function testInterface()
    {
        self::assertInstanceOf(ResolverInterface::class, $this->getInstance());
    }

    #[DataProvider('createGetProcessor')]
    public function testGetProcessor($argument)
    {
        $processors = [
            $this->getProcessor(),
            $this->getProcessor(),
            $this->getProcessor(),
            $this->getProcessor(),
        ];

        $this->registry->expects($this->exactly(2))
            ->method('hasProcessors')
            ->with($this->callback(function ($value) {
                $this->assertContainsEquals($value, [
                    TestParent::class,
                    TestChild::class,
                ]);

                return true;
            }))
            ->willReturn(true);

        $this->registry->expects($this->exactly(2))
            ->method('getProcessors')
            ->willReturnMap([
                [TestParent::class, [$processors[0], $processors[1]]],
                [TestChild::class, [$processors[2], $processors[3]]],
            ]);

        $this->factory->expects($this->once())
            ->method('createProcessor')
            ->with($this->identicalTo($processors))
            ->willReturn($processor = $this->getResolvedProcessor());

        $resolver = $this->getInstance();

        self::assertSame($processor, $resolver->getProcessor($argument));
        self::assertSame($processor, $resolver->getProcessor($argument));
    }

    public static function createGetProcessor()
    {
        return [
            'string' => [TestChild::class],
            'object' => [new TestChild()],
        ];
    }

    public function testGetProcessorNothingFound()
    {
        $this->registry->expects($this->once())
            ->method('hasProcessors')
            ->with(TestClass::class)
            ->willReturn(false);

        $this->registry->expects($this->never())
            ->method('getProcessors');

        $this->factory->expects($this->once())
            ->method('createProcessor')
            ->with([])
            ->willReturn($processor = $this->getResolvedProcessor());

        self::assertSame($processor, $this->getInstance()->getProcessor(TestClass::class));
    }

    public function testGetProcessorInvalidArgument()
    {
        $this->expectException(\Integrated\Common\Normalizer\Exception\ExceptionInterface::class);

        $this->getInstance()->getProcessor(42);
    }

    public function testGetProcessorInvalidClass()
    {
        $this->registry->expects($this->never())
            ->method('getProcessors');

        $this->factory->expects($this->once())
            ->method('createProcessor')
            ->willReturn($processor = $this->getResolvedProcessor());

        self::assertSame($processor, $this->getInstance()->getProcessor('this-is-a-invalid-class'));
    }

    /**
     * @return ProcessorResolver
     */
    protected function getInstance()
    {
        return new ProcessorResolver($this->registry, $this->factory);
    }

    /**
     * @return ProcessorInterface|MockObject
     */
    protected function getProcessor()
    {
        return $this->createMock(ProcessorInterface::class);
    }

    /**
     * @return ResolvedProcessorFactoryTest|MockObject
     */
    protected function getResolvedProcessor()
    {
        return $this->createMock(ResolvedProcessorInterface::class);
    }
}
