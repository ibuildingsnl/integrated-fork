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

    public function __construct(MediaGalleryMenu $mediaGalleryMenu, UserManagerInterface $userManager, ContentProvider $provider, ObjectRepository $repository, AuthorizationCheckerInterface $authorizationChecker)
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
        $this->dm = $this->getDoctrineODM()->getManager();

        $channelAuthorisations = $this->getChannels();

        $menuItems = $this->getMenuItems();

        $menuResult = [];
        $this->makeParentChildRelations($menuItems, $menuResult);

        //Alle content typen ophalen, ook custom
        $uniqueContentTypes = $this->getContentTypes();

        //Set default if needed
        $class_string = $request->query->get('class_string');
        if ($class_string === true || $class_string === null) {
            $class_string = 'File';
            $request->query->set('class_string', "File");
        }

        $media_taxonomy = $request->query->get('media_taxonomy');

        //TODO: vertaling neerzetten in twig template
        $params = $this->getParams($class_string, $media_taxonomy);

        $this->addContentTypesToUserOptions($uniqueContentTypes, $params);

        $newMenu = $this->mediaGalleryMenu->getSimulation();

        $items = $this->provider->getContentFromSolr($request, 100);

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'items' => $items,
            'params' => $params,
            'newMenu' => $newMenu,
            'menuResult' => $menuResult
        ]);
    }

    public function getContentTypeViaFacets() {
        if (false) {
            $q = 'select/?q=*:*&rows=0&facet=on&facet.field=class_string';
            $url = 'https://solr.localhost.e-active.nl/solr/integrated/'; //select/?q=*%3A*&rows=0&facet=on&facet.field=class_string
        }
    }

    public function addContentTypesToUserOptions($uniqueContentTypes, $params) {
        foreach ($uniqueContentTypes as $uniqueContentType) {
            //The following categories are allways there:
            if ($uniqueContentType === 'file' || $uniqueContentType === 'video' || $uniqueContentType === 'image') {
                continue;
            }

            //For new items:
            $params['sort']['options'][$uniqueContentType] = [
                'name' => $uniqueContentType,
                'label' => ucfirst($uniqueContentType)
            ];

            //For filtering:
            $params['types'][$uniqueContentType] = [
                'type' => $uniqueContentType,
                'label' => ucfirst($uniqueContentType)
            ];
        }
    }

    public function getMenuItems() {
        //Alle MediaGalleryMenuTree items ophalen om de categorieen te tonen aan de linkerkant
        $menuItems = [];

        if ($mediaGalleryMenuResult = $this->dm->getRepository(Taxonomy::class)->findBy(['contentType' => 'media_taxonomy'])) {
            foreach ($mediaGalleryMenuResult as $menuItem) {
                $menuItems[] = [
                    "ID" => $menuItem->getId(),
                    "title" => $menuItem->getTitle(),
                    'parent_id' => $menuItem->getParentId()
                ];
            }
        }

        return $menuItems;
    }

    public function getChannels() {
        $channels = [];
        if ($channelResult = $this->dm->getRepository(Channel::class)->findAll()) {
            /** @var $channel \Integrated\Bundle\ContentBundle\Document\Channel\Channel */
            foreach ($channelResult as $channel) {
                $channels[$channel->getId()] = $channel->getName();
            }
        }

        $channelAuthorisations = [];
        foreach ($channels as $index => $value) {
            $read = $this->authorizationChecker->isGranted(PermissionInterface::READ, $value);
            $write = $this->authorizationChecker->isGranted(PermissionInterface::WRITE, $value);

            //TODO Enable this with proper data
//            if ($read === true || $write === true) {
            $channelAuthorisations[] = $value;
//                $channelAuthorisations[$value ] = [
//                    "read" => $read,
//                    "write" => $write
//                ];
//            }
        }

        return $channelAuthorisations;
    }

    public function getParams($class_string, $media_taxonomy) {
        return [
            'sort' => [
                'options' => [
                    "File" => [
                        'name' => 'File',
                        'label' => 'Alle mediabestanden',
                    ],
                    "Image" => [
                        'name' => 'Image',
                        'label' => 'Images'
                    ],
                    "Video" => [
                        'name' => 'Video',
                        'label' => 'Videos'
                    ],
                    "NonMedia" => [
                        'name' => 'NonMedia',
                        'label' => 'Files'
                    ],
                ],
                'current' => $class_string,
                'default' => 'File'
            ],
            //NEW ITEMS
            'types' => [
                "Image" => [
                    'type' => 'image',
                    'label' => 'Image'
                ],
                "Video" => [
                    'type' => 'video',
                    'label' => 'Video'
                ],
                "File" => [
                    'type' => 'file',
                    'label' => 'File'
                ],
            ],
            "media_taxonomy" => [
                "current" => $media_taxonomy,
                "default" => null
            ]
        ];
    }

    public function getContentTypes() {
        $contentTypes = [];
        if ($dbContentTypes = $this->dm->getRepository(File::class)->findAll()) {
            foreach ($dbContentTypes as $menuItem) {
                $contentTypes[] = $menuItem->getContentType();
            }
        }
        return array_unique($contentTypes);
    }

    public function makeParentChildRelations(&$inArray, &$outArray, $currentParentId = 0) {
        if(!is_array($inArray)) {
            return;
        }

        if(!is_array($outArray)) {
            return;
        }

        foreach($inArray as $key => $tuple) {
            if($tuple['parent_id'] == $currentParentId) {
                $tuple['children'] = array();
                $this->makeParentChildRelations($inArray, $tuple['children'], $tuple['ID']);
                $outArray[] = $tuple;
            }
        }
    }

    public function edit(Request $request) {
        echo "Hi Edit";

        $answer = $request->request->get('answer');
        dd($answer);
//        $request->request->get('taxonomy_id');
        dd($request);
    }

    public function menu()
    {
        $dm = $this->getDoctrineODM()->getManager();
        $channels = [];
        if ($channelResult = $dm->getRepository(Channel::class)->findAll()) {
            /** @var $channel \Integrated\Bundle\ContentBundle\Document\Channel\Channel */
            foreach ($channelResult as $channel) {
                $channels[$channel->getId()] = $channel->getName();
            }
        }

        $menuItems = [];
        if ($mediaGalleryMenuResult = $dm->getRepository(Taxonomy::class)->findBy(['contentType' => 'MediaGalleryMenuTree'])) {
            /** @var menuItem \Integrated\Bundle\ContentBundle\Document\Channel\Channel */
            foreach ($mediaGalleryMenuResult as $menuItem) {
//                dd($menuItem);
                $menuItems[menuItem->getId()] = $channel->getName();
            }
        }

        return $this->render('@IntegratedContent/media/menu.html.twig', [
            'channels' => $channels,
//            'params' => $params,
//            'newMenu' => $newMenu
        ]);

//        return $this->redirectToRoute();
//        return $this->redirect(''));
    }
}
