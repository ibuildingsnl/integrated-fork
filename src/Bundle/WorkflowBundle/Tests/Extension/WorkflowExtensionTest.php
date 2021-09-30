<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Integrated\Common\Content\Extension\ExtensionInterface;
use Integrated\Common\Content\Extension\EventSubscriberInterface;
use Integrated\Bundle\WorkflowBundle\Extension\WorkflowExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class WorkflowExtensionTest extends TestCase
{
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testInterface()
    {
        $instance = $this->getInstance();

        $this->assertInstanceOf(ContainerAwareInterface::class, $instance);
        $this->assertInstanceOf(ExtensionInterface::class, $instance);
    }

    public function testGetName()
    {
        $this->assertEquals('integrated.extension.workflow', $this->getInstance()->getName());
    }

    public function testGetSubscribers()
    {
        $subscribers = $this->getInstance()->getSubscribers();

        $this->assertCount(2, $subscribers);
        $this->assertContainsOnlyInstancesOf(EventSubscriberInterface::class, $subscribers);
    }

    protected function getInstance()
    {
        $instance = new WorkflowExtension();
        $instance->setContainer($this->container);

        return $instance;
    }
}
