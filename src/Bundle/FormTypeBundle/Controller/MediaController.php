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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends AbstractController
{
    private \Integrated\Bundle\ContentBundle\Controller\MediaController $controller;

    public function __construct(\Integrated\Bundle\ContentBundle\Controller\MediaController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @Template
     */
    public function image(Request $request): array
    {
        $request->query->set('contenttypes', 'image');

        return $this->controller->indexComponent($request);
    }

    /**
     * @Template
     */
    public function gallery(Request $request): array
    {
        $request->query->set('contenttypes', 'image');

        return $this->controller->indexComponent($request);
    }

    /**
     * @Template
     */
    public function video(Request $request): array
    {
        $request->query->set('contenttypes', 'video');

        return $this->controller->indexComponent($request);
    }
}
