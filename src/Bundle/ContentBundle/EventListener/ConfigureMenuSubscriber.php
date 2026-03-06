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
use Knp\Menu\ItemInterface;
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
    public const MENU_TAXONOMY = 'Taxonomy';
    public const MENU_WEBSITE = 'Website';
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

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::CONFIGURE => [
                ['onMenuConfigureContent', 90],
                ['onMenuConfigureSettings', 10],
                ['onMenuConfigureOrder', -2000],
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
        $menuContent->addChild('Search selections', ['route' => 'integrated_content_search_selection_index']);
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

    public function onMenuConfigureOrder(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== self::MENU) {
            return;
        }

        $this->reorderTopLevelChildren($menu);

        if ($content = $menu->getChild(self::MENU_CONTENT)) {
            $this->reorderChildren($content, [
                'Content navigator',
                'Media Library',
                'Search selections',
            ]);
        }

        if ($website = $menu->getChild(self::MENU_WEBSITE)) {
            $this->reorderChildren($website, [
                'Pages',
                'Blocks',
            ]);
        }

        if ($settings = $menu->getChild(self::MENU_SETTINGS)) {
            $this->reorderChildren($settings, [
                'Brands',
                'Channels',
                'Connectors',
                'Content types',
                'Relations',
                'Workflow',
                'Users',
                'Groups',
                'User scopes',
                'IP List',
                'Scraper',
            ]);
        }
    }

    private function reorderTopLevelChildren(ItemInterface $menu): void
    {
        $children = $menu->getChildren();
        if (\count($children) < 2) {
            return;
        }

        $names = array_keys($children);
        $headOrder = array_flip([
            self::MENU_CONTENT,
            self::MENU_TAXONOMY,
        ]);
        $tailOrder = array_flip([
            self::MENU_WEBSITE,
            self::MENU_SETTINGS,
        ]);

        usort($names, static function (string $left, string $right) use ($headOrder, $tailOrder): int {
            $leftIsHead = isset($headOrder[$left]);
            $rightIsHead = isset($headOrder[$right]);
            if ($leftIsHead !== $rightIsHead) {
                return $leftIsHead ? -1 : 1;
            }
            if ($leftIsHead && $rightIsHead) {
                return $headOrder[$left] <=> $headOrder[$right];
            }

            $leftIsTail = isset($tailOrder[$left]);
            $rightIsTail = isset($tailOrder[$right]);
            if ($leftIsTail !== $rightIsTail) {
                return $leftIsTail ? 1 : -1;
            }
            if ($leftIsTail && $rightIsTail) {
                return $tailOrder[$left] <=> $tailOrder[$right];
            }

            return strcasecmp($left, $right);
        });

        $menu->reorderChildren($names);
    }

    /**
     * @param array<int, string> $preferredOrder
     */
    private function reorderChildren(ItemInterface $menu, array $preferredOrder): void
    {
        $children = $menu->getChildren();
        if (\count($children) < 2) {
            return;
        }

        $names = array_keys($children);
        $preferred = array_flip($preferredOrder);

        usort($names, static function (string $left, string $right) use ($preferred): int {
            $leftPriority = $preferred[$left] ?? \PHP_INT_MAX;
            $rightPriority = $preferred[$right] ?? \PHP_INT_MAX;

            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            return strcasecmp($left, $right);
        });

        $menu->reorderChildren($names);
    }
}
