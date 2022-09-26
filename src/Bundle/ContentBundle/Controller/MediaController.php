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

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\EventListener\ContentChannelIntegrationListener;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryMenu;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Controller\SecurityController;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem as KnpMenuItem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MediaController extends AbstractController
{

    private $mediaGalleryMenu;
    private $userManager;
    private $provider;
    private $repository;
    private $authorizationChecker;

    public function __construct( MediaGalleryMenu $mediaGalleryMenu, UserManagerInterface $userManager, ContentProvider $provider, ObjectRepository $repository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->provider = $provider;
        $this->userManager = $userManager;
        $this->mediaGalleryMenu = $mediaGalleryMenu;
        $this->repository = $repository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //Get all the channels
        /** @var $dm \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->getDoctrineODM()->getManager();
        $channels = [];
        if ($channelResult = $dm->getRepository(Channel::class)->findAll()) {
            /** @var $channel \Integrated\Bundle\ContentBundle\Document\Channel\Channel */
            foreach ($channelResult as $channel) {
                $channels[$channel->getId()] = $channel->getName();
            }
        }

        $channelAuthorisations = [];
        foreach ($channels as $index => $value) {
            $read = $this->authorizationChecker->isGranted(PermissionInterface::READ, $value);
            $write = $this->authorizationChecker->isGranted(PermissionInterface::WRITE, $value);

//            if ($read === true || $write === true) {
            $channelAuthorisations[] = $value;
//                $channelAuthorisations[$value ] = [
//                    "read" => $read,
//                    "write" => $write
//                ];
//            }
        }

        //Alle MediaGalleryMenu items ophalen
        $menuItems = [];
        if ($mediaGalleryMenuResult = $dm->getRepository(Taxonomy::class)->findBy(['contentType' => 'MediaGalleryMenu'])) {
            /** @var menuItem \Integrated\Bundle\ContentBundle\Document\Channel\Channel */
            foreach ($mediaGalleryMenuResult as $menuItem) {
//                dd($menuItem);
                $menuItems[menuItem->getId()] = $channel->getName();
            }
        }

        //Alle content typen ophalen, ook custom
        $contentTypes = [];
        if ($dbContentTypes = $dm->getRepository(File::class)->findAll()) {
//            dd($dbContentTypes);
            foreach ($mediaGalleryMenuResult as $menuItem) {
                dd($menuItem);
                $menuItems[menuItem->getId()] = $channel->getName();
            }
        }

        //TODO: rebuild this into a KPN Menu? NO!
        $class_string = $request->query->get('class_string');

        if ($class_string === true || $class_string === null) {
            $class_string = 'Alle mediabestanden';
        }

        //TODO: vertaling neerzetten in twig template
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

        if (false) {
            //Get facetlist of class_string:
            $facetRequest = new Request;
            $q = 'select/?q=*:*&rows=0&facet=on&facet.field=class_string';
            $facetRequest->query->set('class_string_facets', $q);
            $facets = $this->provider->getContentFromSolr($facetRequest, 20);
            $url = 'https://solr.localhost.e-active.nl/solr/integrated/'; //select/?q=*%3A*&rows=0&facet=on&facet.field=class_string
        }

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
