<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\EventListener;

use Integrated\Bundle\ContentBundle\EventListener\ConfigureMenuSubscriber;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Test for ConfigureMenuSubscriber.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ConfigureMenuSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigureMenuSubscriber
     */
    protected $subscriber;

    /**
     * @var AuthorizationCheckerInterface&MockObject
     */
    protected $authorizationChecker;

    /**
     * @var ConfigureMenuEvent&MockObject
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
        /** @var \Knp\Menu\ItemInterface&MockObject $menu */
        $menu = $this->createMock('Knp\Menu\ItemInterface');

        $this->event
            ->expects($this->once())
            ->method('getMenu')
            ->willReturn($menu);

        $menu
            ->expects($this->once())
            ->method('getName')
            ->willReturn('invalid_menu_name');

        $menu
            ->expects($this->never())
            ->method('getChild');

        $this->subscriber->onMenuConfigureContent($this->event);
    }

    /**
     * Test onMenuConfigure function with valid menu and content menu.
     */
    public function testOnMenuConfigureFunctionWithValidMenuAndContentMenu()
    {
        $menu = $this->getValidMenu($this->event);

        /** @var \Knp\Menu\ItemInterface&MockObject $subMenu */
        $subMenu = $this->createMock('Knp\Menu\ItemInterface');

        $menu
            ->expects($this->once())
            ->method('getChild')
            ->with(ConfigureMenuSubscriber::MENU_CONTENT)
            ->willReturn($subMenu);

        $subMenu
            ->expects($this->atLeastOnce())
            ->method('addChild');

        $this->subscriber->onMenuConfigureContent($this->event);
    }

    /**
     * Test onMenuConfigure function with valid menu and no content menu.
     */
    public function testOnMenuConfigureFunctionWithValidMenuAndNoContentMenu()
    {
        $menu = $this->getValidMenu($this->event);

        /** @var \Knp\Menu\ItemInterface&MockObject $menuContent */
        $menuContent = $this->createMock('Knp\Menu\ItemInterface');

        $menu
            ->expects($this->once())
            ->method('getChild')
            ->with(ConfigureMenuSubscriber::MENU_CONTENT)
            ->willReturn(null);

        $menu
            ->expects($this->once())
            ->method('addChild')
            ->with(ConfigureMenuSubscriber::MENU_CONTENT)
            ->willReturn($menuContent);

        $menuContent
            ->expects($this->once())
            ->method('setExtra')
            ->with('icon', 'iconoir-journal-page')
            ->willReturnSelf();

        $menuContent
            ->expects($this->atLeastOnce())
            ->method('addChild');

        $this->subscriber->onMenuConfigureContent($this->event);
    }

    /**
     * Test onMenuConfigure function with valid menu and with content menu and with manage menu.
     */
    public function testOnMenuConfigureFunctionWithValidMenuAndWithManageMenu()
    {
        $menu = $this->getValidMenu($this->event);

        /** @var \Knp\Menu\ItemInterface&MockObject $menuManage */
        $menuManage = $this->createMock('Knp\Menu\ItemInterface');

        $menu
            ->expects($this->exactly(1))
            ->method('getChild')
            ->willReturnMap([
                [ConfigureMenuSubscriber::MENU_SETTINGS, $menuManage],
            ]);

        $menu
            ->expects($this->never())
            ->method('addChild');

        $menuManage
            ->expects($this->atLeastOnce())
            ->method('addChild');

        $this->authorizationChecker
            ->expects($this->exactly(3))
            ->method('isGranted')
            ->with(ConfigureMenuSubscriber::ROLE_ADMIN)
            ->willReturn(true);

        $this->subscriber->onMenuConfigureSettings($this->event);
    }

    /**
     * Test onMenuConfigure function with valid menu and with no manage menu.
     */
    public function testOnMenuConfigureFunctionWithValidMenuAndWithNoManageMenu()
    {
        $menu = $this->getValidMenu($this->event);

        /** @var \Knp\Menu\ItemInterface&MockObject $menuManage */
        $menuManage = $this->createMock('Knp\Menu\ItemInterface');

        $menu
            ->expects($this->exactly(1))
            ->method('getChild')
            ->willReturnMap([
                [ConfigureMenuSubscriber::MENU_SETTINGS, null],
            ]);

        $menu
            ->expects($this->exactly(1))
            ->method('addChild')
            ->willReturnMap([
                [ConfigureMenuSubscriber::MENU_SETTINGS, [], $menuManage],
            ]);

        $menuManage
            ->expects($this->once())
            ->method('setExtra')
            ->with('icon', 'iconoir-settings')
            ->willReturnSelf()
        ;

        $menuManage
            ->expects($this->atLeastOnce())
            ->method('addChild');

        // Stub isGranted
        $this->authorizationChecker
            ->expects($this->exactly(3))
            ->method('isGranted')
            ->with(ConfigureMenuSubscriber::ROLE_ADMIN)
            ->willReturn(true);

        $this->subscriber->onMenuConfigureSettings($this->event);
    }

    /**
     * Test onMenuConfigure function for channel manager with valid menu and with no content menu and with no manage menu.
     */
    public function testOnMenuConfigureFunctionChannelManagerWithValidMenuAndWithNoManageMenu()
    {
        $menu = $this->getValidMenu($this->event);

        /** @var \Knp\Menu\ItemInterface&MockObject $menuManage */
        $menuManage = $this->createMock('Knp\Menu\ItemInterface');

        $menu
            ->expects($this->exactly(1))
            ->method('getChild')
            ->willReturnMap([
                [ConfigureMenuSubscriber::MENU_SETTINGS, null],
            ]);

        $menu
            ->expects($this->exactly(1))
            ->method('addChild')
            ->willReturnMap([
                [ConfigureMenuSubscriber::MENU_SETTINGS, [], $menuManage],
            ]);

        $menuManage
            ->expects($this->once())
            ->method('setExtra')
            ->with('icon', 'iconoir-settings')
            ->willReturnSelf()
        ;
        $menuManage
            ->expects($this->exactly(1))
            ->method('addChild')
            ->with('Channels');

        // Stub isGranted
        $this->authorizationChecker
            ->expects($this->exactly(4))
            ->method('isGranted')
            ->willReturnMap([
                [ConfigureMenuSubscriber::ROLE_ADMIN, null, false],
                [ConfigureMenuSubscriber::ROLE_CHANNEL_MANAGER, null, true],
            ]);

        $this->subscriber->onMenuConfigureSettings($this->event);
    }

    /**
     * @param ConfigureMenuEvent&MockObject $event
     *
     * @return \Knp\Menu\ItemInterface|MockObject
     */
    protected function getValidMenu($event = null)
    {
        /** @var \Knp\Menu\ItemInterface&MockObject $menu */
        $menu = $this->createMock('Knp\Menu\ItemInterface');

        $menu
            ->expects($this->once())
            ->method('getName')
            ->willReturn(ConfigureMenuSubscriber::MENU);

        if (null !== $event) {
            $event
                ->expects($this->once())
                ->method('getMenu')
                ->willReturn($menu);
        }

        return $menu;
    }
}
