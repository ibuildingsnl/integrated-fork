<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Bulk\Tests\DependencyInjection;

use Integrated\Common\Bulk\DependencyInjection\FactoryRegistryBuilderPass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class FactoryRegistryBuilderPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $service1 = $this->createMock(Definition::class);
        $service2 = $this->createMock(Definition::class);

        $definition = $this->createMock(Definition::class);
        $definition->expects($this->exactly(3))
            ->method('addMethodCall')
            ->with('addFactory', $this->callback(function (array $arguments) use ($service1, $service2) {
                $this->assertContainsEquals($arguments[0], ['class1', 'class2']);
                $this->assertContainsEquals($arguments[1], [$service1, $service2]);

                return true;
            }));

        $container = $this->getContainer();
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('service')
            ->willReturn(true);

        $container->expects($this->exactly(3))
            ->method('getDefinition')
            ->willReturnMap([
                ['service', $definition],
                ['tagged.service.1', $service1],
                ['tagged.service.2', $service2],
            ]);

        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('tag')
            ->willReturn([
                'tagged.service.1' => [['class' => 'class1'], ['class' => 'class2']],
                'tagged.service.2' => [['class' => 'class1']],
            ]);

        $this->getInstance()->process($container);
    }

    public function testProcessServiceNotFound()
    {
        $container = $this->getContainer();
        $container->expects($this->once())
            ->method('hasDefinition')
            ->with('service')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('getDefinition');

        $container->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->getInstance()->process($container);
    }

    /**
     * @return FactoryRegistryBuilderPass
     */
    protected function getInstance()
    {
        return new FactoryRegistryBuilderPass('service', 'tag');
    }

    /**
     * @return ContainerBuilder|MockObject
     */
    protected function getContainer()
    {
        return $this->createMock(ContainerBuilder::class);
    }
}
