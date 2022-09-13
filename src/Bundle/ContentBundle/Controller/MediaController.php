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
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryMenu;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends AbstractController
{

    private $mediaGalleryMenu;
    public function __construct(private ContentProvider $provider, MediaGalleryMenu $mediaGalleryMenu)
    {
        $this->mediaGalleryMenu = $mediaGalleryMenu;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //I think this is correct Autowiring:
        //Include the Service in services.xml
        //Pass the Service as an argument in controller.xml
        $menu = $this->mediaGalleryMenu->get();

        //this sets this in the request:
        // "class" => "Integrated\Bundle\ContentBundle\Document\Content\File"
        $request->query->set('class', File::class);
        $request->query->set('class', Article::class);
        //weird, File and Article both give the same result with me

        $items = $this->provider->getContentFromSolr($request, 20);

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'items' => $items,
            'menu' => $menu
        ]);
    }
}
