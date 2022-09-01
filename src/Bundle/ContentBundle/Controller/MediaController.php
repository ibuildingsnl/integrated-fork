<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends AbstractController
{
    public function __construct(
        private ContentProvider $provider
    ) {
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $request->query->set('class', File::class);
        $items = $this->provider->getContentFromSolr($request, 20);

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'items' => $items,
        ]);
    }
}
