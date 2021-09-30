<?php
/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\MenuBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;

/**
 * Test for ConfigureMenuEvent.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ConfigureMenuEventTest extends TestCase
{
    /**
     * @var ConfigureMenuEvent
     */
    protected $event;

    /**
     * @var FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $menu;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->factory = $this->createMock(FactoryInterface::class);
        $this->menu = $this->createMock(ItemInterface::class);
        $this->event = new ConfigureMenuEvent($this->factory, $this->menu);
    }

    /**
     * Test instanceOf.
     */
    public function testInstanceOf()
    {
        $this->assertInstanceOf(Event::class, $this->event);
    }

    /**
     * Test getFactory function.
     */
    public function testGetFactoryFunction()
    {
        $this->assertSame($this->factory, $this->event->getFactory());
    }

    /**
     * Test getMenu function.
     */
    public function testGetMenuFunction()
    {
        $this->assertSame($this->menu, $this->event->getMenu());
    }
}
