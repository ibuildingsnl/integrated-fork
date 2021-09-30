<?php
/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Knp\Menu\ItemInterface;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Integrated\Bundle\UserBundle\EventListener\ConfigureMenuSubscriber;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Test for ConfigureMenuSubscriber.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ConfigureMenuSubscriberTest extends TestCase
{
    /**
     * @var ConfigureMenuSubscriber
     */
    protected $subscriber;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationChecker;

    /**
     * @var ConfigureMenuEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * Setup the test.
     */
    protected function setUp(): void
    {
        $this->event = $this->getMockBuilder(ConfigureMenuEvent::class)->disableOriginalConstructor()->getMock();
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->subscriber = new ConfigureMenuSubscriber($this->authorizationChecker);
    }

    /**
     * Test getSubscribedEvents.
     */
    public function testGetSubscribedEventsFunction()
    {
        $this->assertArrayHasKey('integrated_menu.configure', ConfigureMenuSubscriber::getSubscribedEvents());
    }

    /**
     * Test onMenuConfigure function with invalid menu.
     */
    public function testOnMenuConfigureFunctionWithInvalidMenu()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $menu */
        $menu = $this->createMock(ItemInterface::class);

        $this->event
            ->expects($this->once())
            ->method('getMenu')
            ->willReturn($menu)
        ;

        $menu
            ->expects($this->once())
            ->method('getName')
            ->willReturn('invalid_menu_name')
        ;

        $menu
            ->expects($this->never())
            ->method('getChild')
        ;

        $this->subscriber->onMenuConfigure($this->event);
    }

    /**
     * Test onMenuConfigure function with valid menu and with manage menu.
     */
    public function testOnMenuConfigureFunctionWithValidMenuAndWithManageMenu()
    {
        $menu = $this->getValidMenu($this->event);

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $menuContent */
        $menuManage = $this->createMock(ItemInterface::class);

        $menu
            ->expects($this->once())
            ->method('getChild')
            ->with(ConfigureMenuSubscriber::MENU_MANAGE)
            ->willReturn($menuManage)
        ;

        $menu
            ->expects($this->never())
            ->method('addChild')
        ;

        $menuManage
            ->expects($this->atLeastOnce())
            ->method('addChild')
        ;

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with(ConfigureMenuSubscriber::ROLE_ADMIN)
            ->willReturn(true)
        ;

        $this->subscriber->onMenuConfigure($this->event);
    }

    /**
     * Test onMenuConfigure function with valid menu and with no manage menu.
     */
    public function testOnMenuConfigureFunctionWithValidMenuAndWithNoManageMenu()
    {
        $menu = $this->getValidMenu($this->event);

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $menuContent */
        $menuManage = $this->createMock(ItemInterface::class);

        $menu
            ->expects($this->once())
            ->method('getChild')
            ->with(ConfigureMenuSubscriber::MENU_MANAGE)
            ->willReturn(null)
        ;

        $menu
            ->expects($this->once())
            ->method('addChild')
            ->with(ConfigureMenuSubscriber::MENU_MANAGE)
            ->willReturn($menuManage)
        ;
        $menuManage
            ->expects($this->atLeastOnce())
            ->method('addChild')
        ;

        // Stub isGranted
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with(ConfigureMenuSubscriber::ROLE_ADMIN)
            ->willReturn(true)
        ;

        $this->subscriber->onMenuConfigure($this->event);
    }

    /**
     * @param ConfigureMenuEvent|\PHPUnit_Framework_MockObject_MockObject $event
     *
     * @return ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getValidMenu($event = null)
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $menu */
        $menu = $this->createMock(ItemInterface::class);

        $menu
            ->expects($this->once())
            ->method('getName')
            ->willReturn(ConfigureMenuSubscriber::MENU)
        ;

        if (null !== $event) {
            $event
                ->expects($this->once())
                ->method('getMenu')
                ->willReturn($menu)
            ;
        }

        return $menu;
    }
}
