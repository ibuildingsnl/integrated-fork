<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Tests\Solr\Extension;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State;
use Integrated\Bundle\WorkflowBundle\Solr\Extension\WorkflowExtension;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Converter\Container;
use Integrated\Common\Converter\ContainerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Integrated\Bundle\WorkflowBundle\Solr\Extension\WorkflowExtension
 *
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class WorkflowExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResolverInterface&MockObject
     */
    private $resolver;

    /**
     * @var ObjectRepository&MockObject
     */
    private $workflow;

    /**
     * @var ObjectRepository&MockObject
     */
    private $definition;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(ResolverInterface::class);
        $this->workflow = $this->createMock('Doctrine\Persistence\ObjectRepository');
        $this->definition = $this->createMock('Doctrine\Persistence\ObjectRepository');
    }

    public function testInterface()
    {
        self::assertInstanceOf('Integrated\\Common\\Converter\\Type\\TypeExtensionInterface', $this->getInstance());
    }

    #[DataProvider('buildProvider')]
    public function testBuild(Definition\State $state, array $expected)
    {
        $content = $this->getContent();
        $container = $this->getContainer();

        $this->resolver->expects($this->atLeastOnce())
            ->method('hasType')
            ->willReturn(true);

        $this->resolver->expects($this->atLeastOnce())
            ->method('getType')
            ->with($this->equalTo('this-is-the-content-type'))
            ->willReturn($this->getContentType('this-is-the-workflow-id'));

        $this->workflow->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with($this->identicalTo(['content' => $content]))
            ->willReturn($this->getWorkflow($state));

        $this->definition->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->build($container, $content);

        self::assertEquals($expected, $container->toArray());
    }

    public static function buildProvider()
    {
        return [
            [
                self::getState(),
                ['workflow_deadline' => [0 => 0]],
            ],
            [
                self::getState([self::getPermission('group1', false, false), self::getPermission('group2', false, false)]),
                ['workflow_deadline' => [0 => 0]],
            ],
            [
                self::getState([self::getPermission('group1', true, false), self::getPermission('group2', false, true)]),
                ['security_workflow_read' => ['group1'], 'security_workflow_write' => ['group2'], 'workflow_deadline' => [0 => 0]],
            ],
            [
                self::getState([self::getPermission('group1', false, false), self::getPermission('group2', true, true)]),
                ['security_workflow_read' => ['group2'], 'security_workflow_write' => ['group2'], 'workflow_deadline' => [0 => 0]],
            ],
            [
                self::getState([self::getPermission('group1', true, true), self::getPermission('group2', true, true)]),
                ['security_workflow_read' => ['group1', 'group2'], 'security_workflow_write' => ['group1', 'group2'], 'workflow_deadline' => [0 => 0]],
            ],
            [
                self::getState([self::getPermission('group1', true, false), self::getPermission('group2', false, false)]),
                ['security_workflow_read' => ['group1'], 'workflow_deadline' => [0 => 0]],
            ],
            [
                self::getState([self::getPermission('group1', false, false), self::getPermission('group2', false, true)]),
                ['security_workflow_write' => ['group2'], 'workflow_deadline' => [0 => 0]],
            ],
        ];
    }

    public function testBuildNoContent()
    {
        $container = $this->createMock('Integrated\\Common\\Converter\\ContainerInterface');
        $container->expects($this->never())
            ->method($this->anything());

        /* @var ContainerInterface $container */

        $this->getInstance()->build($container, new \stdClass());
    }

    public function testBuildNoContentType()
    {
        $container = $this->getContainer();

        $this->resolver->expects($this->never())
            ->method('hasType')
            ->willReturn(false);

        $this->workflow->expects($this->never())
            ->method($this->anything());

        $this->definition->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->build($container, $this->getContent());

        self::assertEquals([], $container->toArray());
    }

    public function testBuildNoWorkflow()
    {
        $container = $this->getContainer();

        $this->resolver->expects($this->atLeastOnce())
            ->method('hasType')
            ->willReturn(true);

        $this->resolver->expects($this->atLeastOnce())
            ->method('getType')
            ->with($this->equalTo('this-is-the-content-type'))
            ->willReturn($this->getContentType());

        $this->workflow->expects($this->never())
            ->method($this->anything());

        $this->definition->expects($this->never())
            ->method($this->anything());

        $this->getInstance()->build($container, $this->getContent());

        self::assertEquals([], $container->toArray());
    }

    #[DataProvider('buildProvider')]
    public function testBuildNoCurrentState(Definition\State $state, array $expected)
    {
        $content = $this->getContent();
        $container = $this->getContainer();

        $this->resolver->expects($this->atLeastOnce())
            ->method('hasType')
            ->willReturn(true);

        $this->resolver->expects($this->atLeastOnce())
            ->method('getType')
            ->with($this->equalTo('this-is-the-content-type'))
            ->willReturn($this->getContentType('this-is-the-workflow-id'));

        $this->workflow->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with($this->identicalTo(['content' => $content]))
            ->willReturn($this->getWorkflow());

        $this->definition->expects($this->once())
            ->method('find')
            ->with($this->equalTo('this-is-the-workflow-id'))
            ->willReturn($this->getDefinition($state));

        $this->getInstance()->build($container, $content);

        self::assertEquals($expected, $container->toArray());
    }

    #[DataProvider('buildProvider')]
    public function testBuildDefaultWorkflow(Definition\State $state, array $expected)
    {
        $content = $this->getContent();
        $container = $this->getContainer();

        $this->resolver->expects($this->atLeastOnce())
            ->method('hasType')
            ->willReturn(true);

        $this->resolver->expects($this->atLeastOnce())
            ->method('getType')
            ->with($this->equalTo('this-is-the-content-type'))
            ->willReturn($this->getContentType('this-is-the-workflow-id'));

        $this->workflow->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with($this->identicalTo(['content' => $content]))
            ->willReturn(null);

        $this->definition->expects($this->once())
            ->method('find')
            ->with($this->equalTo('this-is-the-workflow-id'))
            ->willReturn($this->getDefinition($state));

        $this->getInstance()->build($container, $content);

        self::assertEquals($expected, $container->toArray());
    }

    public function testBuildNoDefaultWorkflow()
    {
        $content = $this->getContent();
        $container = $this->getContainer();

        $this->resolver->expects($this->atLeastOnce())
            ->method('hasType')
            ->willReturn(true);

        $this->resolver->expects($this->atLeastOnce())
            ->method('getType')
            ->with($this->equalTo('this-is-the-content-type'))
            ->willReturn($this->getContentType('this-is-the-workflow-id'));

        $this->workflow->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with($this->identicalTo(['content' => $content]))
            ->willReturn(null);

        $this->definition->expects($this->once())
            ->method('find')
            ->with($this->equalTo('this-is-the-workflow-id'))
            ->willReturn(null);

        $this->getInstance()->build($container, $content);

        self::assertEquals([], $container->toArray());
    }

    public function testBuildNoDefaultWorkflowState()
    {
        $content = $this->getContent();
        $container = $this->getContainer();

        $this->resolver->expects($this->atLeastOnce())
            ->method('hasType')
            ->willReturn(true);

        $this->resolver->expects($this->atLeastOnce())
            ->method('getType')
            ->with($this->equalTo('this-is-the-content-type'))
            ->willReturn($this->getContentType('this-is-the-workflow-id'));

        $this->workflow->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with($this->identicalTo(['content' => $content]))
            ->willReturn(null);

        $this->definition->expects($this->once())
            ->method('find')
            ->with($this->equalTo('this-is-the-workflow-id'))
            ->willReturn($this->getDefinition());

        $this->getInstance()->build($container, $content);

        self::assertEquals([], $container->toArray());
    }

    public function testBuildRemovedPrevious()
    {
        $container = $this->getContainer();

        $container->set('security_workflow_read', 'this-should-be-removed');
        $container->set('security_workflow_write', 'this-should-be-removed');

        $this->resolver->expects($this->never())
            ->method('hasType')
            ->willReturn(false);

        $this->getInstance()->build($container, $this->getContent());

        self::assertEquals([], $container->toArray());
    }

    public function testGetName()
    {
        self::assertEquals('integrated.content', $this->getInstance()->getName());
    }

    /**
     * @return WorkflowExtension
     */
    protected function getInstance()
    {
        return new WorkflowExtension($this->resolver, $this->workflow, $this->definition);
    }

    /**
     * @return ContentInterface
     */
    protected function getContent()
    {
        $mock = $this->createMock(ContentInterface::class);
        $mock->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('this-is-the-content-type');

        return $mock;
    }

    /**
     * @param string $workflow
     *
     * @return ContentTypeInterface
     */
    protected function getContentType($workflow = null)
    {
        $mock = $this->createMock(ContentTypeInterface::class);
        $mock->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('workflow'))
            ->willReturn($workflow);

        $mock->expects($this->any())
            ->method('getPermissions')
            ->willReturn([]);

        return $mock;
    }

    /**
     * @return State
     */
    protected function getWorkflow(?Definition\State $state = null)
    {
        $mock = $this->createMock(State::class);
        $mock->expects($this->atLeastOnce())
            ->method('getState')
            ->willReturn($state);

        return $mock;
    }

    /**
     * @return Definition
     */
    protected function getDefinition(?Definition\State $state = null)
    {
        $mock = $this->createMock(Definition::class);
        $mock->expects($this->atLeastOnce())
            ->method('getDefault')
            ->willReturn($state);

        return $mock;
    }

    /**
     * @param Definition\Permission[] $permissions
     */
    protected static function getState(array $permissions = []): Definition\State
    {
        $state = new Definition\State();

        foreach ($permissions as $permission) {
            $state->addPermission($permission);
        }

        return $state;
    }

    protected static function getPermission(string $group, bool $read, bool $write): Definition\Permission
    {
        $permission = new Definition\Permission();
        $permission->setGroup($group);

        if ($read) {
            $permission->addMask($permission::READ);
        }

        if ($write) {
            $permission->addMask($permission::WRITE);
        }

        return $permission;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        // Easier to check end result when using an actual container instead of mocking it away. Also
        // the code coverage for the container class is ignored for these tests.

        return new Container();
    }
}
