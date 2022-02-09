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
use Integrated\Bundle\MenuBundle\Menu\DatabaseMenuFactory;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Bundle\MenuBundle\Provider\IntegratedMenuProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class MenuController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function renderMenu(Request $request, DatabaseMenuFactory $menuFactory)
    {
        $data = (array) json_decode($request->getContent(), true);
        $menu = null;

        if (isset($data['data'])) {
            $menu = $menuFactory->fromArray($data['data']);
        }

        return $this->render('@IntegratedWebsite/menu/render.'.$request->getRequestFormat('json').'.twig', [
            'menu' => $menu,
            'options' => isset($data['options']) ? $data['options'] : [],
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function save(Request $request, DocumentManager $documentManager, IntegratedMenuProvider $menuProvider, DatabaseMenuFactory $menuFactory, ChannelContextInterface $channelContext)
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $data = (array) json_decode($request->getContent(), true);

        if (isset($data['menu'])) {
            foreach ((array) $data['menu'] as $array) { // support multiple menu's
                if ($menu = $menuFactory->fromArray((array) $array)) {
                    if ($menu2 = $menuProvider->get($menu->getName())) {
                        $menu2->setChildren($menu->getChildren());
                    } else {
                        $menu->setChannel($channelContext->getChannel());

                        $documentManager->persist($menu);
                    }
                }
            }

            $documentManager->flush();
        }

        return new JsonResponse();
    }
}
