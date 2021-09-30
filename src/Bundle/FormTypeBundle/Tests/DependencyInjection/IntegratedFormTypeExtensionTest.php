<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\FormTypeBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Integrated\Bundle\FormTypeBundle\DependencyInjection\IntegratedFormTypeExtension;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class IntegratedFormTypeExtensionTest extends TestCase
{
    /**
     * @var IntegratedFormTypeExtension
     */
    protected $extension;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->extension = new IntegratedFormTypeExtension();
    }

    /**
     * Test load function.
     */
    public function testLoadFunction()
    {
        // Create config
        $config = [];

        /* @var $parameterBag \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface | \PHPUnit_Framework_MockObject_MockObject */
        $parameterBag = $this->createMock(ParameterBagInterface::class);

        /* @var $container \Symfony\Component\DependencyInjection\ContainerBuilder | \PHPUnit_Framework_MockObject_MockObject */
        $container = $this->createMock(ContainerBuilder::class);

        // Stub getParameterBag function
        $container->expects($this->once())
            ->method('getParameterBag')
            ->willReturn($parameterBag);

        // Load config
        $this->extension->load($config, $container);
    }
}
