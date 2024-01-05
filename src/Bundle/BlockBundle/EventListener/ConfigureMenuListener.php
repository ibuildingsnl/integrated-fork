<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\EventListener;

use Integrated\Bundle\BlockBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Michael Jongman <michael@e-active.nl>
 */
class ConfigureMenuListener implements EventSubscriberInterface
{
    public const MENU = 'integrated_menu';
    public const MENU_WEBSITE = 'Website';
    public const ROLE_WEBSITE_MANAGER = 'ROLE_WEBSITE_MANAGER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var FilterQueryProvider
     */
    private $filterQueryProvider;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        FilterQueryProvider $filterQueryProvider,
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->filterQueryProvider = $filterQueryProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::CONFIGURE => 'onMenuConfigure',
        ];
    }

    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        if (!$this->authorizationChecker->isGranted(self::ROLE_WEBSITE_MANAGER) &&
            !$this->authorizationChecker->isGranted(self::ROLE_ADMIN)) {
            $session = $this->requestStack->getSession();
            $hasBlocks = $session->get('hasBlocks', false);

            if (!$session->has('hasBlocks') && $user = $this->tokenStorage->getToken()->getUser()) {
                $hasBlocks = (bool) \count($this->filterQueryProvider->getBlockIds([], $user));
                $session->set('hasBlocks', $hasBlocks);
            }

            if (!$hasBlocks) {
                return;
            }
        }

        $menu = $event->getMenu();

        if ($menu->getName() !== self::MENU) {
            return;
        }

        if (!$label = $menu->getChild(self::MENU_WEBSITE)) {
            $label = $menu->addChild(self::MENU_WEBSITE);
        }

        $label->addChild('Blocks', ['route' => 'integrated_block_block_index']);
    }
}
