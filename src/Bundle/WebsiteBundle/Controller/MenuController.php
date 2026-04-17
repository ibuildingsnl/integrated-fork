<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\MenuBundle\Document\Menu;
use Integrated\Bundle\MenuBundle\Event\MenuChangedEvent;
use Integrated\Bundle\MenuBundle\Menu\DatabaseMenuFactory;
use Integrated\Bundle\MenuBundle\Provider\IntegratedMenuProvider;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    private DocumentManager $documentManager;
    private IntegratedMenuProvider $menuProvider;
    private DatabaseMenuFactory $menuFactory;
    private ChannelContextInterface $channelContext;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DocumentManager $documentManager,
        IntegratedMenuProvider $menuProvider,
        DatabaseMenuFactory $menuFactory,
        ChannelContextInterface $channelContext,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->documentManager = $documentManager;
        $this->menuProvider = $menuProvider;
        $this->menuFactory = $menuFactory;
        $this->channelContext = $channelContext;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function renderMenu(Request $request): Response
    {
        $data = (array) json_decode($request->getContent(), true);
        $options = isset($data['options']) ? (array) $data['options'] : [];
        $menu = null;

        if (isset($data['data'])) {
            $menu = $this->menuFactory->fromArray($data['data']);
        }

        // In edit mode we still need a root menu object so the UI can render an add-item placeholder.
        if (!$menu && !empty($options['editMode'])) {
            $name = 'menu';
            if (isset($data['data']) && \is_array($data['data']) && !empty($data['data']['name'])) {
                $name = (string) $data['data']['name'];
            }

            $menu = $this->menuFactory->createItem($name);
        }

        return $this->render('@IntegratedWebsite/menu/render.'.$request->getRequestFormat('json').'.twig', [
            'menu' => $menu,
            'options' => $options,
        ]);
    }

    public function save(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $data = (array) json_decode($request->getContent(), true);
        $changedMenus = [];

        if (isset($data['menu'])) {
            foreach ((array) $data['menu'] as $array) { // support multiple menu's
                $sanitized = $this->sanitizeMenuArray((array) $array, true);
                if (!$sanitized) {
                    continue;
                }

                if ($menu = $this->menuFactory->fromArray($sanitized)) {
                    if ($this->menuProvider->has($menu->getName())) {
                        $menu2 = $this->menuProvider->get($menu->getName());
                        if (!$menu2 instanceof Menu) {
                            continue;
                        }

                        $menu2->setChildren($menu->getChildren());
                        $changedMenus[] = $menu2;
                    } else {
                        $menu->setChannel($this->channelContext->getChannel());

                        $this->documentManager->persist($menu);
                        $changedMenus[] = $menu;
                    }
                }
            }

            $this->documentManager->flush();

            foreach ($changedMenus as $menu) {
                $this->eventDispatcher->dispatch(new MenuChangedEvent($menu));
            }
        }

        return new JsonResponse();
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>|null
     */
    private function sanitizeMenuArray(array $item, bool $isRoot = false): ?array
    {
        $children = [];
        foreach ((array) ($item['children'] ?? []) as $child) {
            $sanitizedChild = $this->sanitizeMenuArray((array) $child);
            if ($sanitizedChild) {
                $children[] = $sanitizedChild;
            }
        }
        $item['children'] = $children;

        if ($isRoot) {
            return $item;
        }

        $name = trim((string) ($item['name'] ?? ''));
        $uri = trim((string) ($item['uri'] ?? ''));
        $searchSelection = trim((string) ($item['searchSelection'] ?? ''));
        $hasChildren = \count($children) > 0;

        $isPlaceholder = ($name === '' || $name === '+')
            && ($uri === '' || $uri === '#')
            && $searchSelection === ''
            && !$hasChildren;

        return $isPlaceholder ? null : $item;
    }
}
