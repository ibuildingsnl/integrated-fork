<?php

namespace Integrated\Bundle\TaxonomyBundle\EventListener;

use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ConfigureMenuSubscriber implements EventSubscriberInterface
{
    public const MENU = 'integrated_menu';
    public const MENU_TAXONOMIES = 'Taxonomy';

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ContentTypeManager $contentTypes,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::CONFIGURE => [
                ['onMenuConfigure', 80],
            ],
        ];
    }

    public function onMenuConfigure(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== self::MENU) {
            return;
        }

        $taxonomyTypes = $this->contentTypes->filterInstanceOf(Taxonomy::class);

        foreach ($taxonomyTypes as $taxonomyType) {
            if (!$this->authorizationChecker->isGranted(PermissionInterface::WRITE, $taxonomyType)) {
                continue;
            }
            $menuAdmin = $menu->getChild(self::MENU_TAXONOMIES);
            if (!$menuAdmin) {
                $menuAdmin = $menu->addChild(self::MENU_TAXONOMIES)->setExtra('icon', 'iconoir-label');
            }
            $menuAdmin->addChild($taxonomyType->getName(), [
                'route' => 'integrated_taxonomy_index',
                'routeParameters' => ['type' => $taxonomyType->getId()],
            ]);
        }
    }
}
