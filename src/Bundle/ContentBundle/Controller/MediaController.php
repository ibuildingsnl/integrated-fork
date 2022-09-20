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
        //TODO: Check output, currently receiving more than just files -> company for example.
        $class_string = $request->query->get('class_string');

        if ($class_string === true || $class_string === null) {
            $class_string = 'Alle mediabestanden';
        }

        $params = [
            'sort' => [
                'options' => [
                    "Alle mediabestanden" => [
                        'name' => 'File',
                        'label' => 'Alle mediabestanden',
                    ],
                    "Image" => [
                        'name' => 'Image',
                        'label' => 'Images'
                    ],
                    "Video"  => [
                        'name' => 'Video',
                        'label' => 'Videos'
                    ],
                    "NonMedia"  => [
                        'name' => 'NonMedia',
                        'label' => 'Files'
                    ],
                ],
                'current' => $class_string,
                'default' => 'File'
            ],
            'types' => [
                "Image" => [
                    'type' => 'image',
                    'label' => 'Image'
                ],
                "Video"  => [
                    'type' => 'video',
                    'label' => 'Video'
                ],
                "File"  => [
                    'type' => 'file',
                    'label' => 'File'
                ],
            ]
        ];

        //I think this is correct Autowiring:
        //Include the Service in services.xml
        //Pass the Service as an argument in controller.xml
        $newMenu = $this->mediaGalleryMenu->getSimulation();

        //Get facetlist of class_string:
        $facetRequest = new Request;
        $q = 'select/?q=*:*&rows=0&facet=on&facet.field=class_string';
        $facetRequest->query->set('class_string_facets', $q);
        $facets = $this->provider->getContentFromSolr($facetRequest, 20);
//        dd($facets);
        $url = 'https://solr.localhost.e-active.nl/solr/integrated/'; //select/?q=*%3A*&rows=0&facet=on&facet.field=class_string



        //TODO get class_string value from request
//        $request->query->set('class_string', $params["sort"]["options"][$class_string]["name"];);

        $items = $this->provider->getContentFromSolr($request, 20);

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'items' => $items,
            'params' => $params,
            'newMenu' => $newMenu
        ]);
    }
}
