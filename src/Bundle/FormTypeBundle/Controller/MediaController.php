<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\FormTypeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends AbstractController
{
    private \Integrated\Bundle\ContentBundle\Controller\MediaController $controller;

    public function __construct(\Integrated\Bundle\ContentBundle\Controller\MediaController $controller)
    {
        $this->controller = $controller;
    }

    public function image(Request $request): Response
    {
        if ($contentType = $request->query->get('contenttypes')) {
            $request->query->set('contenttypes', [$contentType]);
        }

        return $this->render('@IntegratedFormType/media/image.html.twig', $this->controller->indexComponent($request));
    }

    public function gallery(Request $request): Response
    {
        if ($contentType = $request->query->get('contenttypes')) {
            $request->query->set('contenttypes', [$contentType]);
        }

        return $this->render('@IntegratedFormType/media/gallery.html.twig', $this->controller->indexComponent($request));
    }

    public function video(Request $request): Response
    {
        if ($contentType = $request->query->get('contenttypes')) {
            $request->query->set('contenttypes', [$contentType]);
        }

        return $this->render('@IntegratedFormType/media/video.html.twig', $this->controller->indexComponent($request));
    }
}
