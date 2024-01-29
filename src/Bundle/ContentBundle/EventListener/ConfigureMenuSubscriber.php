<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Event subscriber for adding menu items to integrated_menu.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ConfigureMenuSubscriber implements EventSubscriberInterface
{
    public const MENU = 'integrated_menu';
    public const MENU_CONTENT = 'Content';
    public const MENU_SETTINGS = 'Settings';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_CHANNEL_MANAGER = 'ROLE_CHANNEL_MANAGER';

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::CONFIGURE => [
                ['onMenuConfigureContent', 90],
                ['onMenuConfigureSettings', 10],
            ],
        ];
    }

    public function onMenuConfigureContent(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== self::MENU) {
            return;
        }

        if (!$menuContent = $menu->getChild(self::MENU_CONTENT)) {
            $menuContent = $menu->addChild(self::MENU_CONTENT)->setExtra('icon', 'iconoir-journal-page');
        }

        $menuContent->addChild('Content navigator', ['route' => 'integrated_content_content_index']);
        $menuContent->addChild('Media Library', ['route' => 'integrated_content_media_index']);
    }

    public function onMenuConfigureSettings(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== self::MENU) {
            return;
        }

        if ($this->authorizationChecker->isGranted(self::ROLE_ADMIN) || $this->authorizationChecker->isGranted(self::ROLE_CHANNEL_MANAGER)) {
            if (!$menuManage = $menu->getChild(self::MENU_SETTINGS)) {
                $menuManage = $menu->addChild(self::MENU_SETTINGS)->setExtra('icon', 'iconoir-settings');
            }

            if ($this->authorizationChecker->isGranted(self::ROLE_ADMIN)) {
                $menuManage->addChild('Content types', ['route' => 'integrated_content_content_type_index']);
            }

            $menuManage->addChild('Channels', ['route' => 'integrated_content_channel_index']);

            if ($this->authorizationChecker->isGranted(self::ROLE_ADMIN)) {
                $menuManage->addChild('Relations', ['route' => 'integrated_content_relation_index']);
            }
        }
    }
}
