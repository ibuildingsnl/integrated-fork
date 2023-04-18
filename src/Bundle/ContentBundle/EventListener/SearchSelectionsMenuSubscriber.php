<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\MongoDBException;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelectionRepository;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Integrated\Bundle\UserBundle\Model\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class SearchSelectionsMenuSubscriber implements EventSubscriberInterface
{
    public const MENU = 'integrated_menu';
    public const MENU_CONTENT = 'Content';

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SearchSelectionRepository $searchSelections,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::CONFIGURE => 'onMenuConfigure',
        ];
    }

    /**
     * @throws MongoDBException
     */
    public function onMenuConfigure(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        if ($menu->getName() !== self::MENU) {
            return;
        }

        if (!$menuContent = $menu->getChild(self::MENU_CONTENT)) {
            $menuContent = $menu->addChild(self::MENU_CONTENT)->setExtra('icon', 'iconoir-journal-page');
        }
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        foreach ($this->searchSelections->findPublicByUserId($user->getId()) as $item) {
            if ($item->isInMenu()) {
                $menuContent->addChild($item->getTitle(), [
                    'route' => 'integrated_content_content_index',
                    'routeParameters' => ['searchSelection' => $item->getId()],
                ]);
            }
        }
    }
}
