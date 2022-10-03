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
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\EventListener\ContentChannelIntegrationListener;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryMenu;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\PageBundle\Form\Type\MediaConnectType;
use Integrated\Bundle\UserBundle\Controller\SecurityController;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem as KnpMenuItem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;

class MediaController extends AbstractController
{

    private $mediaGalleryMenu;
    private $userManager;
    private $provider;
    private $repository;
    private $authorizationChecker;
    private $queueSubscriber;
    private $indexer;

    public function __construct(MediaGalleryMenu $mediaGalleryMenu, UserManagerInterface $userManager, ContentProvider $provider, ObjectRepository $repository, AuthorizationCheckerInterface $authorizationChecker, QueueSubscriber $queueSubscriber, IndexerInterface $indexer)
    {
        $this->provider = $provider;
        $this->userManager = $userManager;
        $this->mediaGalleryMenu = $mediaGalleryMenu;
        $this->repository = $repository;
        $this->authorizationChecker = $authorizationChecker;
        $this->queueSubscriber = $queueSubscriber;
        $this->indexer = $indexer;
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

        $media_taxonomy = $request->query->get('MediaTaxonomy');
        $request->query->set('MediaTaxonomy[]', $media_taxonomy);

        //TODO: vertaling neerzetten in twig template
        $params = $this->getParams($class_string, $media_taxonomy);

        $paramsExtended = $this->addContentTypesToUserOptions($uniqueContentTypes, $params);

        $newMenu = $this->mediaGalleryMenu->getSimulation();

        $items = $this->provider->getContentFromSolr($request, 100);

        $request->query->remove('MediaTaxonomy');

        $test = new ContentType;


//        $form = $this->createForm(
//            ContentType::class,
//            ['categoryID' => "1"],
//            [
//                'action' => $this->generateUrl('integrated_user_iplist_new'),
//                'method' => 'POST',
//            ]
//        );
//
//        $task = new MediaConnectType;
//        $task->categoryID = 5;
//        $form = $this->createForm(MediaConnectType::class, (new Taxonomy()));
//        $task = new \stdClass();
//        $task->task = '';

//        $form = $this->createForm( MediaConnectType::class, $task);

//            ->add('task', TextType::class)
//            ->add('save', SubmitType::class, ['label' => 'Create Task'])
//            ->getForm();

        return $this->render('@IntegratedContent/media/index.html.twig', [
//            'form' => $form,
            'items' => $items,
            'params' => $paramsExtended,
            'newMenu' => $newMenu,
            'menuResult' => $menuResult
        ]);
    }

    public function getChannels()
    {
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

    public function getMenuItems()
    {
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

    public function makeParentChildRelations(&$inArray, &$outArray, $currentParentId = 0)
    {
        if (!is_array($inArray)) {
            return;
        }

        if (!is_array($outArray)) {
            return;
        }

        foreach ($inArray as $key => $tuple) {
            if ($tuple['parent_id'] == $currentParentId) {
                $tuple['children'] = array();
                $this->makeParentChildRelations($inArray, $tuple['children'], $tuple['ID']);
                $outArray[] = $tuple;
            }
        }
    }

    public function getContentTypes()
    {
        $contentTypes = [];
        if ($dbContentTypes = $this->dm->getRepository(File::class)->findAll()) {
            foreach ($dbContentTypes as $menuItem) {
                $contentTypes[] = $menuItem->getContentType();
            }
        }
        return array_unique($contentTypes);
    }

    public function getParams($class_string, $media_taxonomy)
    {
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

    public function addContentTypesToUserOptions($uniqueContentTypes, $params)
    {
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

        return $params;
    }

    public function getContentTypeViaFacets()
    {
        if (false) {
            $q = 'select/?q=*:*&rows=0&facet=on&facet.field=class_string';
            $url = 'https://solr.localhost.e-active.nl/solr/integrated/'; //select/?q=*%3A*&rows=0&facet=on&facet.field=class_string
        }
    }

    public function addChannel(Request $request)
    {
        echo "AddChannel";

        dd($request);
    }

    public function addCategpry(Request $request)
    {
        echo "addCategpry";

        dd($request);
    }

    //Update relation of mediaItems
    public function edit(Request $request)
    {
        $messages = [];

        $this->dm = $this->getDoctrineODM()->getManager();

        $params = json_decode($request->getContent(), true);
//      "media_id" => "daf99de93f2f3d5e97306bbab4ae5abb"               REQUIRED, one or many
//      "category_id" => "category_2-1"                                OPTIONAL, one
//      "channel_id" => "3324234"                                      OPTIONAL, one

        //get the Taxonomy (Category) with $params["category_id"]
        $taxonomy = null;
        if ($params["category_id"]) {
            $taxonomy = $this->dm->getRepository(Taxonomy::class)->find($params["category_id"]);
        }

        //get one or more media items with:
        $mediaItems = $this->dm->getRepository(File::class)->findBy(array('id' => $params["media_id"]));

        foreach ($mediaItems as $mediaItem) {
            if ($relation = $mediaItem->getRelation('mediaitem_channelcategory')) {
                $messages[] = 'there is a relation';
            } else {
                $messages[] = 'new relation';
                $relation = (new Relation())
                    ->setRelationId('mediaitem_channelcategory')
                    ->setRelationType('taxonomy');
            }

            //Check if references already contain this id:
            $relationIDs = $relation->getReferences()->map(function ($item) {
                return $item->getID();
            })->toArray();
            if (in_array($taxonomy->getID(), $relationIDs)) {
                $messages[] = 'relation already exists';
            } else {
                $messages[] = 'setting the new relation';
                // Add the new taxonomy item
                $relation->addReference($taxonomy);
                $mediaItem->addRelation($relation);
                $this->dm->persist($mediaItem);
                $this->dm->flush();
                $this->updateQueueToSolr($mediaItem);
            }
        }

        return new JsonResponse(['$messages' => $messages]);
    }

    public function updateQueueToSolr($content) {
        dump("running solr update q");
        $queue = $this->queueSubscriber->getQueue();
        $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);
        $this->dm->persist($content);
        $this->dm->flush();
        $this->indexer->setOption('queue.size', 2);
        $this->indexer->execute();
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
