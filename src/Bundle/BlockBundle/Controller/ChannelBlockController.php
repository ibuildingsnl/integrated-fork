<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Security\AllowedBlockClassProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelBlockController extends AbstractController
{
    private DocumentManager $manager;
    private AllowedBlockClassProvider $allowedBlockClassProvider;

    public function __construct(DocumentManager $documentManager, AllowedBlockClassProvider $allowedBlockClassProvider)
    {
        $this->manager = $documentManager;
        $this->allowedBlockClassProvider = $allowedBlockClassProvider;
    }

    public function new(Request $request): Response
    {
        if (!(
            ($this->isGranted('ROLE_WEBSITE_MANAGER') || $this->isGranted('ROLE_ADMIN'))
            && $this->isCsrfTokenValid('create-channel-block', $request->request->get('csrf_token')))
        ) {
            throw $this->createAccessDeniedException();
        }

        $class = $request->request->get('class');
        $id = $request->request->get('id');
        $name = $request->request->get('name');

        if (
            !\is_string($class)
            || $class === ''
            || !class_exists($class)
            || !is_subclass_of($class, Block::class)
            || !$this->allowedBlockClassProvider->isAllowed($class)
        ) {
            throw $this->createNotFoundException(\sprintf('Invalid block "%s"', (string) $class));
        }

        try {
            $block = new $class($id);
        } catch (\Throwable) {
            throw $this->createNotFoundException(\sprintf('Invalid block "%s"', $class));
        }

        $block->setTitle($name);
        $block->setLayout('default.html.twig');

        $this->manager->persist($block);
        $this->manager->flush();

        return new JsonResponse(['result' => 'ok']);
    }
}
