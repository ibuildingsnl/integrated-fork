<?php

namespace Integrated\Bundle\TaxonomyBundle\EventListener;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ConfigureMenuSubscriber implements EventSubscriberInterface
{
    public const MENU = 'integrated_menu';
    public const MENU_TAXONOMIES = 'Taxonomy';
    private const ROLE_CHANNEL_MANAGER = 'ROLE_CHANNEL_MANAGER';

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ObjectRepository $contentTypes,
    ) {}

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [ConfigureMenuEvent::CONFIGURE => 'onMenuConfigure'];
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== self::MENU) {
            return;
        }

        $taxonomyType = $this->contentTypes->find('taxonomy');

        if ($taxonomyType && (
            $this->authorizationChecker->isGranted(self::ROLE_CHANNEL_MANAGER) ||
            $this->authorizationChecker->isGranted(PermissionInterface::READ, $taxonomyType)
        )) {
            $menuAdmin = $menu->addChild(self::MENU_TAXONOMIES);
            $menuAdmin->addChild('Taxonomies', ['route' => '@todo']);
        }
    }
}
