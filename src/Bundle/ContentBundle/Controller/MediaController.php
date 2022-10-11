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
use Integrated\Bundle\ContentBundle\Document\Content\Content;
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

/*
 * Goal for the user:
 *  - show files in a coherent manner
 *  - let the user filter / search for specific content
 *  - let the user be able to organise with categories and channels
 *
 * Codewise:
 *  - Media items are ContentType of the sort: File.
 *  - A Media item can be a Video, Image, File(NonMedia), or a custom type. But they are all extended from file.
 *  - To work with database, you are working with the classnames, so: Image, Video, CustomContentType
 *  - To work with url, you work with camelcase, so: image, custom_content_type
 *
 *
 */

class MediaController extends AbstractController
{
    private $mediaGalleryMenu;
    private $userManager;
    private $provider;
    private $repository;
    private $authorizationChecker;
    private $queueSubscriber;
    private $indexer;

    //settings:
    private $showFilesOfSubcategories = true;
    private $notShownFileTypes = ['jpg', 'jpeg', 'png', 'tif', 'webp', 'MP4', 'MOV', 'AVI', 'FLV', 'MKV', 'WMV'];

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
    //TODO: vertaling neerzetten in twig template
    public function index(Request $request)
    {
        $this->dm = $this->getDoctrineODM()->getManager();

        $only_allowed_taxonomy_id = $request->query->get('media_taxonomy_id');

        $menu = $this->createMenu();

        if ($only_allowed_taxonomy_id !== NULL && $this->showFilesOfSubcategories === true) {
            $allSelectedCategoryTitles = $this->findSelectedMenuTitles($menu, $only_allowed_taxonomy_id);
            $request->query->set('MediaTaxonomy', $allSelectedCategoryTitles);
        }

        //Is this needed? not sure anymore
        $request->query->set('MediaTaxonomy[]', $request->query->get('MediaTaxonomy'));

        $uniqueContentTypes = $this->getContentTypes();

        $this->setAndGetClassString($request);

        $this->setYearMonthFilter($request);

        $items = $this->provider->getContentFromSolr($request, 2000);

        if ($only_allowed_taxonomy_id !== NULL) {
            $items2 = array_filter($items, function ($item) use ($only_allowed_taxonomy_id) {
                return array_filter($item->getRelations()->toArray(), function ($relation) use ($only_allowed_taxonomy_id) {
                    if ($relation->getRelationId() === 'mediaitem_channelcategory') {
                        return $relation->getReferences()->filter(function ($reference) use ($only_allowed_taxonomy_id) {
                                return $reference->getID() === $only_allowed_taxonomy_id;
                            })->count() > 0;
                    }
                });
            });
        }

        $filtered_items_2 = array_filter($items, function ($item) use ($only_allowed_taxonomy_id) {
            return array_filter($item->getRelations()->toArray(), function ($relation) use ($only_allowed_taxonomy_id) {
                if ($relation->getRelationId() === 'mediaitem_channelcategory') {
                    return array_filter($relation->getReferences()->toArray(), function ($reference) use ($only_allowed_taxonomy_id) {
                        return $reference->getID() === $only_allowed_taxonomy_id;
                    });
                }
            });
        });

        $filtered_items = [];
        foreach ($items as $item) {
            $addToResult = false;
//            dd($item->getFile()->getMetadata()->getExtension());
            foreach ($item->getRelations() as $relation) {
                if ($relation->getRelationId() === 'mediaitem_channelcategory') {
                    foreach ($relation->getReferences() as $reference) {
                        if ($reference->getID() === $only_allowed_taxonomy_id) {
                            $addToResult = true;
                        }
                    }
                }
            }
            if ($addToResult === true) {
                $filtered_items[] = $item;
            }
        }

        $dateFilter = $this->getYearMonthDates($request);

        $request->query->remove('MediaTaxonomy[]');
        $request->query->remove('MediaTaxonomy');

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'not_shown_filetypes' => array_map(fn($item) => strtolower($item),
                $this->notShownFileTypes
             ),
            'items' => $items,
            'params' => $this->getParams($request, $uniqueContentTypes, $dateFilter),
            'menu' => $menu,
        ]);
    }

    public function createMenu()
    {
        $menuItems = $this->getMenuItems();

        $menuResult = [];
        $this->makeParentChildRelations($menuItems, $menuResult);

        return $menuResult;
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

    public function findCurrentlySelectedMenu($inArray, $target)
    {
        foreach ($inArray as $key => $tuple) {
            if ($tuple["ID"] === $target) {
                return $tuple;
            }

            if (count($tuple['children']) > 0) {
                $found = $this->findCurrentlySelectedMenu($tuple["children"], $target);

                if ($found !== NULL) {
                    return $found;
                }
            }
        }
    }

    public function getContentTypes()
    {
        //TODO: Make sure File and or Files are shown correctly. Not sure if it shows both File and Files due to data.
        $contentTypeNames = array_map([$this, 'getContentTypeName'], $this->dm->getRepository(ContentType::class)->findAll());

        return array_filter($contentTypeNames);

        //old
        $contentTypes = [];
        if ($dbContentTypes = $this->dm->getRepository(File::class)->findAll()) {
            foreach ($dbContentTypes as $menuItem) {
                $contentTypes[] = $menuItem->getContentType();
            }
        }
        return array_unique($contentTypes);
    }

    //specific day:
    //                    xx                      xx
    //            2022-09-17T00:00:00Z TO 2022-09-17T23:59:59Z
    //specific month:
    //                 xx                      xx
    //            2022-09-01T00:00:00Z TO 2022-10-01T00:00:00Z

    public function setAndGetClassString($request)
    {
        //we want to keep two things separate:
        // - what the user asks for
        // - what we query
        // because with the user selection 'Alle Mediabestanden' we want to query for the class: File.
        // but when the user clicks on 'Files' we want to query on 'NonMedia'

        $class_string = $request->query->get('class_string');
        if ($class_string === null || $class_string === "" || $class_string === 'Alle mediabestanden') {
            $request->query->set('class_string', "Alle mediabestanden");
            $request->query->set('solr_class_string', "File");
        } else if ($class_string === 'NonMedia' || $class_string === 'Image' || $class_string === 'Video') {
            $request->query->set('solr_class_string', $class_string);
        } else {
            $camelCase = $this->KebabToCamel($class_string);
            $request->query->set('solr_class_string', $camelCase);
        }

        return $class_string;
    }

    public function KebabToCamel($input)
    {
        return strtolower(str_replace(' ', '_', ucwords(str_replace('_', ' ', $input))));
    }

    public function setYearMonthFilter($request)
    {
        $yearMonthFilter = $request->query->get('year_month');
        if ($yearMonthFilter == '') {
            $request->query->set('year_month', 'Alles');
            $yearMonthFilter = $request->query->get('year_month');
        }

        if (isset($yearMonthFilter) && $yearMonthFilter != null && $yearMonthFilter !== 'Alles') {
            list($year, $month, $day) = explode('-', $yearMonthFilter);
            $startDate = "{$year}-{$month}-{$day}T00:00:00Z";
            $endDate = "{$year}-{$month}-{$day}T23:59:59Z";
            $fullDateFilter = $startDate . ' TO ' . $endDate;
            $request->query->set('year_month_day_filter', $fullDateFilter);

        } else if ($yearMonthFilter === 'Alles') {
            $request->query->set('year_month_day_filter', '1000-01-01T00:00:00Z TO 3000-09-17T23:59:59Z');
        }

        return $yearMonthFilter;
    }

    public function getYearMonthDates(Request $request): array
    {
        $date_amount = $this->provider->getFilterOptionsFromSolr($request);

        return $this->transformDateYearToFrontendArray($date_amount);
    }

    public function transformDateYearToFrontendArray($dates)
    {
        $result = [];
        foreach ($dates as $yearMonth => $amount) {
            $result[$yearMonth] = [
                'label' => $yearMonth . " (" . $amount . ")",
                'yearMonth' => $yearMonth,
                'amount' => $amount
            ];
        }

        return $result;
    }

    public function getParams($request, $uniqueContentTypes, $dateFilter)
    {
        $params = [
            'date_filter' => [
                'options' => [
                    "Alles" => [
                        'type' => 'Alles',
                        'name' => 'Alles',
                        'label' => 'Alles',
                    ],
                ],
                'current' => $request->query->get('year_month'),
                'default' => "Alles"
            ],
            'content_types' => [
                'options' => [
                    "Alle mediabestanden" => [
                        'name' => 'Alle mediabestanden',
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
                'current' => $request->query->get('class_string'),
                'default' => "Alle mediabestanden"
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
                "current" => $request->query->get('MediaTaxonomy'),
                "default" => null
            ]
        ];

        $paramsExtended = $this->addContentTypesToUserOptions($uniqueContentTypes, $params, $dateFilter);

        return $this->checkIfCurrentExistsAsKey($paramsExtended);
    }

    public function addContentTypesToUserOptions($uniqueContentTypes, $params, $dateFilter)
    {
        foreach ($uniqueContentTypes as $uniqueContentType) {
            //The following categories are allways there, and dont need to be added again.
            if ($uniqueContentType === 'file' || $uniqueContentType === 'video' || $uniqueContentType === 'image') {
                continue;
            }

            //For new items:
            $params['content_types']['options'][$uniqueContentType] = [
                'name' => $uniqueContentType,
                'label' => ucfirst($uniqueContentType)
            ];

            //For filtering:
            $params['types'][$uniqueContentType] = [
                'type' => $uniqueContentType,
                'label' => ucfirst($uniqueContentType)
            ];
        }

        foreach ($dateFilter as $yearMonth) {
            $params['date_filter']["options"][$yearMonth["yearMonth"]] = [
                'type' => $yearMonth["yearMonth"],
                'label' => $yearMonth["label"],
                'name' => $yearMonth["label"]
            ];
        }

        return $params;
    }

    public function checkIfCurrentExistsAsKey($paramsExtended)
    {
        $current_date = $paramsExtended["date_filter"]["current"];

        if (key_exists($current_date, $paramsExtended["date_filter"]["options"]) === false) {
            $paramsExtended["date_filter"]["current"] = 'Alles';
        }

        $current_content_type = $paramsExtended["content_types"]["current"];
        if (key_exists($current_content_type, $paramsExtended["content_types"]["options"]) === false) {
            $paramsExtended["content_types"]["current"] = null;
        }

        return $paramsExtended;
    }

    public function getDatesOfItems($items)
    {
        $dates = [];
        foreach ($items as $item) {
            $yearMonth = $item->getCreatedAt()->format('Y-m-d');
            if (array_key_exists($yearMonth, $dates)) {
                $dates[$yearMonth]++;
            } else {
                $dates[$yearMonth] = 1;
            }
        }

        return $this->transformDateYearToFrontendArray($dates);
    }

    public function array_column_recursive(array $haystack, $needle)
    {
        $found = [];
        array_walk_recursive($haystack, function ($value, $key) use (&$found, $needle) {
            if ($key == $needle)
                $found[] = $value;
        });
        return $found;
    }

    public function findSelectedMenuTitles(array $menu, string $only_allowed_taxonomy_id): array
    {
        $allSelectedTaxonomys = $this->findCurrentlySelectedMenu($menu, $only_allowed_taxonomy_id);

        //TODO change this to ID!
        return $this->array_column_recursive($allSelectedTaxonomys, 'title');
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

            //TODO Enable this when we have proper data
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

    public function getContentTypeName($item)
    {
        $className = $item->getClass();
        if (str_contains($className, '\Content\File') ||
            str_contains($className, '\Content\Video') ||
            str_contains($className, '\Content\Image')
        ) {
            return $item->getName();
        }
    }

    //Update relation of mediaItems
    //I want to keep the messages for debugging
    public function manageRelations(Request $request)
    {
        $this->dm = $this->getDoctrineODM()->getManager();
        $messages = [];
        $params = json_decode($request->getContent(), true);
//      "media_id" => "daf99de93f2f3d5e97306bbab4ae5abb"               REQUIRED, one or many
//      "category_id" => "category_2-1"                                OPTIONAL, one
//      "category_id_origin" => "3324234"                              OPTIONAL, one

        //Is the user dragging from and to the same folder
        //TODO disable this at client side
        if ($params["category_id"] === $params["category_id_origin"]) {
            return new JsonResponse('Origin is same as target');
        }

        //get the Taxonomy (Category) with $params["category_id"]
        $taxonomy = null;
        if ($params["category_id"]) {
            $taxonomy = $this->dm->getRepository(Taxonomy::class)->find($params["category_id"]);
        }

        //if media id is a string, convert it to an array
        //(is_array($params["media_id"]) === false) ? $params["media_id"] = [$params["media_id"]] : '';
        //more readable
        if (is_array($params["media_id"]) === false) {
            $params["media_id"] = [$params["media_id"]];
        }

        $mediaItems = $this->dm->createQueryBuilder(File::class)
            ->field('id')->in($params["media_id"])
            ->getQuery()
            ->execute();

        foreach ($mediaItems as $mediaItem) {
            if ($relations = $mediaItem->getRelation('mediaitem_channelcategory')) {
                $messages[] = "relation exists?";
            } else {
                $messages[] = "new relation?";
                $relations = (new Relation())
                    ->setRelationId('mediaitem_channelcategory')
                    ->setRelationType('taxonomy');
            }

            //Check if references already contain this id:
            $relationIDs = $relations->getReferences()->map(function ($item) {
                return $item->getID();
            })->toArray();

            //Remove relation when needed:
            $category_id_origin = $params["category_id_origin"];
            if ($category_id_origin !== "") {
                $messages[] = 'origin: ' . $category_id_origin;
                $messages[] = 'relationIDs: ' . implode("-", $relationIDs);

                if (in_array($category_id_origin, $relationIDs)) {
                    $remove_this_taxonomy = $this->dm->getRepository(Taxonomy::class)->find($category_id_origin);
                    $relations->removeReference($remove_this_taxonomy);
                    $messages[] = 'Removed';
                }
            }

            if (in_array($taxonomy->getID(), $relationIDs)) {
                $messages[] = 'in array';
            } else {
                $messages[] = 'not in array';
                // Add the new taxonomy item
                $relations->addReference($taxonomy);
                $mediaItem->addRelation($relations);
                $this->dm->persist($mediaItem);
                $this->dm->flush();
                $this->updateQueueToSolr($mediaItem);
            }
        }

        return new JsonResponse($messages);
    }

    public function updateQueueToSolr($content)
    {
        $queue = $this->queueSubscriber->getQueue();
        $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);
        $this->dm->persist($content);
        $this->dm->flush();
        $this->indexer->setOption('queue.size', 2); //1 voor het item, 1 voor de commit message
        $this->indexer->execute();
    }

    //TODO later make this
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
        ]);
    }
}
