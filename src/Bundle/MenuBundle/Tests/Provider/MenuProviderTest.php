<?php
/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\MenuBundle\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use InvalidArgumentException;
use Knp\Menu\ItemInterface;
use Integrated\Bundle\MenuBundle\Provider\MenuProvider;

/**
 * Test for Provider.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class MenuProviderTest extends TestCase
{
    /**
     * @var string
     */
    public const VALID_MENU = 'integrated_menu';

    /**
     * @var string
     */
    public const INVALID_MENU = 'invalid_menu';

    /**
     * @var MenuProvider
     */
    protected $provider;

    /**
     * @var FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->factory = $this->createMock(FactoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->provider = new MenuProvider($this->factory, $this->eventDispatcher);
    }

    /**
     * Test instanceOf.
     */
    public function testInstanceOf()
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->provider);
    }

    /**
     * Test has function with invalid menu.
     */
    public function testHasFunctionWithInvalidMenu()
    {
        $this->assertFalse($this->provider->has(self::INVALID_MENU));
    }

    /**
     * Test has function with valid menu.
     */
    public function testHasFunctionWithValidMenu()
    {
        $this->assertTrue($this->provider->has(self::VALID_MENU));
    }

    /**
     * Test get function with invalid menu.
     */
    public function testGetFunctionWithInvalidMenu()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->provider->get(self::INVALID_MENU);
    }

    /**
     * Test get function twice with valid menu.
     */
    public function testGetFunctionTwiceWithValidMenu()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $menu */
        $menu = $this->createMock(ItemInterface::class);

        $this->factory
            ->expects($this->once())
            ->method('createItem')
            ->with(self::VALID_MENU)
            ->willReturn($menu)
        ;

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
        ;

        $this->provider->get(self::VALID_MENU);
        $this->provider->get(self::VALID_MENU);
    }
}
