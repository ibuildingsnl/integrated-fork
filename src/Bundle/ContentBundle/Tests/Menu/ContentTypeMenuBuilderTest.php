<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Menu;

use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Menu\ContentTypeMenuBuilder;
use Integrated\Bundle\ContentBundle\Tests\Menu\FakeContent\ItemWithoutParent;
use Integrated\Bundle\ContentBundle\Tests\Menu\FakeContent\ParentWithMultipleLevels\AbstractItemA\ItemA;
use Integrated\Bundle\ContentBundle\Tests\Menu\FakeContent\ParentWithMultipleLevels\ItemB;
use Integrated\Bundle\ContentBundle\Tests\Menu\FakeContent\ParentWithOneLevel\Item;
use Integrated\Common\ContentType\Iterator;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ContentTypeMenuBuilderTest extends TestCase
{
    /**
     * @var FactoryInterface|MockObject
     */
    protected $factory;

    /**
     * @var ContentTypeManager|MockObject
     */
    protected $contentTypeManager;

    /**
     * @var AuthorizationCheckerInterface|MockObject
     */
    protected $authorizationChecker;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->factory = $this->createMock(FactoryInterface::class);
        $this->contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)->disableOriginalConstructor()->getMock();
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
    }

    /**
     * Test createMenu function with invalid content types.
     */
    public function testCreateMenuFunctionWithInvalidContentType()
    {
        $builder = $this->getInstance();

        /** @var ItemInterface|MockObject $menu */
        $menu = $this->createMock(ItemInterface::class);

        $this->factory
            ->expects($this->once())
            ->method('createItem')
            ->with('root')
            ->willReturn($menu)
        ;

        $this->contentTypeManager
            ->expects($this->once())
            ->method('getAll')
            ->willReturn(new Iterator([$this->createMock('\stdClass')]))
        ;

        $this->assertSame($menu, $builder->createMenu());
    }

    /**
     * Test createMenu function with item without parent.
     */
    public function testCreateMenuFunctionWithItemWithoutParent()
    {
        $builder = $this->getInstance();

        /** @var ItemInterface|MockObject $menu */
        $menu = $this->createMock(ItemInterface::class);

        $this->factory
            ->expects($this->once())
            ->method('createItem')
            ->with('root')
            ->willReturn($menu)
        ;

        $this->contentTypeManager
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($this->getItemWithoutParent())
        ;

        /** @var ItemInterface|MockObject $child */
        $child = $this->createMock(ItemInterface::class);

        $child
            ->expects($this->once())
            ->method('addChild')
        ;

        $menu
            ->expects($this->once())
            ->method('addChild')
            ->with('ItemWithoutParent')
            ->willReturn($child)
        ;

        $this->assertSame($menu, $builder->createMenu());
    }

    /**
     * Test createMenu function with items.
     */
    public function testCreateMenuFunctionWithItems()
    {
        $builder = $this->getInstance();

        /** @var ItemInterface|MockObject $menu */
        $menu = $this->createMock(ItemInterface::class);

        $this->factory
            ->expects($this->once())
            ->method('createItem')
            ->with('root')
            ->willReturn($menu)
        ;

        $this->contentTypeManager
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($this->getItems())
        ;

        /** @var ItemInterface|MockObject $child1 */
        $child1 = $this->createMock(ItemInterface::class);

        $child1
            ->expects($this->exactly(2))
            ->method('addChild')
        ;

        /** @var ItemInterface|MockObject $child2 */
        $child2 = $this->createMock(ItemInterface::class);

        $child2
            ->expects($this->once())
            ->method('addChild')
        ;

        $menu
            ->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnMap([
                ['ParentWithMultipleLevels', [], $child1],
                ['ParentWithOneLevel', [], $child2],
            ])
        ;

        $this->assertSame($menu, $builder->createMenu());
    }

    /**
     * Test createMenu function with access check.
     */
    public function testCreateMenuFunctionWithItemsWithAccessCheck()
    {
        $builder = $this->getInstance(true);

        $items = $this->getItems();

        /** @var ItemInterface|MockObject $menu */
        $menu = $this->createMock(ItemInterface::class);

        $this->factory
            ->expects($this->once())
            ->method('createItem')
            ->with('root')
            ->willReturn($menu)
        ;

        $this->contentTypeManager
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($items)
        ;

        $this->authorizationChecker
            ->expects($this->exactly(3))
            ->method('isGranted')
            ->willReturnOnConsecutiveCalls(
                true,
                false,
                false
            )
        ;

        /** @var ItemInterface|MockObject $child1 */
        $child1 = $this->createMock(ItemInterface::class);

        $child1
            ->expects($this->once())
            ->method('addChild')
        ;

        /** @var ItemInterface|MockObject $child2 */
        $child2 = $this->createMock(ItemInterface::class);

        $child2
            ->expects($this->never())
            ->method('addChild')
        ;

        $menu
            ->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnMap([
                ['ParentWithMultipleLevels', [], $child1],
                ['ParentWithOneLevel', [], $child2],
            ])
        ;

        $this->assertSame($menu, $builder->createMenu());
    }

    protected function getItemWithoutParent(): Iterator
    {
        $contentType = $this->createMock(ContentType::class);
        $contentType
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(ItemWithoutParent::class)
        ;

        return new Iterator([$contentType]);
    }

    protected function getItems(): Iterator
    {
        $contentType1 = $this->createMock(ContentType::class);
        $contentType1
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(Item::class)
        ;

        $contentType2 = $this->createMock(ContentType::class);
        $contentType2
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(ItemA::class)
        ;

        $contentType3 = $this->createMock(ContentType::class);
        $contentType3
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(ItemB::class)
        ;

        return new Iterator([$contentType1, $contentType2, $contentType3]);
    }

    protected function getInstance(bool $withFilter = false): ContentTypeMenuBuilder
    {
        $builder = new ContentTypeMenuBuilder(
            $this->factory,
            $this->contentTypeManager,
            $this->authorizationChecker
        );

        if (!$withFilter) {
            $this->authorizationChecker
                ->method('isGranted')
                ->willReturn(true)
            ;
        }

        return $builder;
    }
}
