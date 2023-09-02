<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ConfigureMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $permission
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::CONFIGURE => ['onMenuConfigure', -999],
        ];
    }

    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== 'integrated_menu') {
            return;
        }
        $settings = $menu->getChild('Settings');
        if (!$settings) {
            return;
        }

        if (
            $this->permission->isGranted('ROLE_ADMIN') ||
            $this->permission->isGranted('ROLE_CHANNEL_MANAGER')
        ) {
            // Out with the old,
            $settings->removeChild('Channels');
            $settings->removeChild('Connectors');
            // In with the new
            $settings->addChild('Brands', ['route' => 'integrated_content_brand_index']);
        }
    }
}
