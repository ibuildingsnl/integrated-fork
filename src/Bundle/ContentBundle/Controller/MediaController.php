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
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryMenu;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

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
 */

class MediaController extends AbstractController
{
    public const PAGINATOR_LIMIT = 50;
    public const DATE_FILTER_ON = '+1MONTH'; //1DAY or 1MONTH
    public const SHOW_FILES_OF_SUBCATEGORY = false; // true is not fully implemented yet. Missing: properly handle the relations when dragging from and to categories
    public const NOT_SHOWN_FILETYPES = ['jpg', 'jpeg', 'png', 'tif', 'webp', 'mp4', 'mov', 'avi', 'flv', 'mkv', 'wmv'];
    public const HARD_CODED_CATEGORY = 'MediaTaxonomy';
    public const DEFAULT_FILE_TYPES = [
        'image' => [
            'label_singular' => 'Image',
            'label_plural' => 'Images',
            'solr_name' => 'Image',
            'new_type_location' => 'image',
            'class_name' => 'Image',
            'class_path' => 'Integrated\Bundle\ContentBundle\Document\Content\Image',
        ],
        'video' => [
            'label_singular' => 'Video',
            'label_plural' => 'Videos',
            'solr_name' => 'Video',
            'new_type_location' => 'video',
            'class_name' => 'Video',
            'class_path' => 'Integrated\Bundle\ContentBundle\Document\Content\Video',
        ],
        'file' => [
            'label_singular' => 'File',
            'label_plural' => 'Files',
            'solr_name' => 'NonMedia',
            'new_type_location' => 'file',
            'class_name' => 'File',
            'class_path' => 'Integrated\Bundle\ContentBundle\Document\Content\File',
        ],
    ];
    public const SOLR_ALL_MEDIA_CLASS_STRING = "File";
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
    // TODO: Use translations in Twig
    // TODO: Either work with ID`s or do some checks that a category has a unique name
    public function index(Request $requestSource)
    {
        $requestCopy = $this->setAndGetClassString($requestSource);
        $params = [];
        $this->dm = $this->getDoctrineODM()->getManager();

        $menu = $this->mediaGalleryMenu->createMenu($this);

        $otherMenu = $this->mediaGalleryMenu->getMenuItemsFromDB();

        // TODO: With my installation, I cant add groups, work this out later
        foreach ($otherMenu as $menuItem) {
            if (false === $this->authorizationChecker->isGranted(PermissionInterface::READ, $menuItem)) {
//                dump("false");
                continue;
            } else {
//                dump("true");
            }
        }

        if (true === $this::SHOW_FILES_OF_SUBCATEGORY && null !== $requestCopy->query->get('media_taxonomy_id')) {
            $allSelectedCategoryTitles = $this->mediaGalleryMenu->findSelectedMenuTitles($menu, $requestCopy->query->get('media_taxonomy_id'));
            $requestCopy->query->set($this::HARD_CODED_CATEGORY, $allSelectedCategoryTitles);
        }

        $uniqueContentTypes = $this->getContentTypes();

        $this->setYearMonthFilter($requestCopy);

        $items = $this->provider->getContentFromSolr($requestCopy, 2000);

        $dateFilter = $this->getYearMonthDates($requestCopy);

        $params = array_merge($params, $this->getParams($requestCopy, $uniqueContentTypes, $dateFilter));

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'paginator' => $this->createPaginator($items, $requestSource),
            'items' => $items,
            'params' => $params,
            'menu' => $menu,
            'not_shown_filetypes' => array_map(
                fn($item) => strtolower($item),
                $this::NOT_SHOWN_FILETYPES
            ),
        ]);
    }

    /**
     * @param $request
     *
     * @return mixed
     *               specific day:
     *               --------xx                      xx
     *               2022-09-17T00:00:00Z TO 2022-09-17T23:59:59Z
     *               specific month:
     *               -----xx                      xx
     *               2022-09-01T00:00:00Z TO 2022-10-01T00:00:00Z
     */
    public function setAndGetClassString($requestSource)
    {
        /** we want to keep two things separate:
         * - what the user asks for
         * - what we query
         * because with the user selection 'Alle Mediabestanden' we want to query for the class: File.
         * but when the user clicks on 'Files' we want to query on 'NonMedia'.
         */
        $request = clone $requestSource;

        if (null !== $requestSource->query->get('MediaTaxonomy')) {
            $request->query->set('MediaTaxonomy', [$requestSource->query->get('MediaTaxonomy')]);
            $request->query->set('MediaTaxonomy[]', [$request->query->get('MediaTaxonomy')]);
        }

        $classString = $request->query->get('class_string');
        if (null === $classString || '' === $classString || 'all_files' === $classString) {
            $request->query->set('class_string', 'all_files');
            $request->query->set('solr_class_string', $this::SOLR_ALL_MEDIA_CLASS_STRING);

        } elseif (\in_array($classString, array_keys($this::DEFAULT_FILE_TYPES))) {
            $request->query->set('solr_class_string', $this::DEFAULT_FILE_TYPES[$classString]["solr_name"]);
        } else {
            $camelCase = $this->kebabToCamel($classString);
            $request->query->set('solr_class_string', $camelCase);
        }

        return $request;
    }

    public function kebabToCamel($input): string
    {
        return strtolower(str_replace(' ', '_', ucwords(str_replace('_', ' ', $input))));
    }

    public function getContentTypes()
    {
        // TODO: Make sure File and or Files are shown correctly. Not sure if it shows both File and Files due to data.
        $contentTypeNames = array_map([$this, 'getContentTypeName'], $this->dm->getRepository(ContentType::class)->findAll());

        return array_filter($contentTypeNames);
    }

    public function setYearMonthFilter($request)
    {
        $yearMonthFilter = $request->query->get('year_month');

        if (null === $yearMonthFilter) {
            return;
        } else if ('all_dates' === $yearMonthFilter) {
            $request->query->set('year_month_day_filter', '1000-01-01T00:00:00Z TO 3000-09-17T23:59:59Z');
        } else {
            if ($this::DATE_FILTER_ON == '+1DAY') {
                if (isset($yearMonthFilter) && null != $yearMonthFilter && 'all_dates' !== $yearMonthFilter) {
                    list($year, $month, $day) = explode('-', $yearMonthFilter);
                    $startDate = "{$year}-{$month}-{$day}T00:00:00Z";
                    $endDate = "$year-{$month}-{$day}T23:59:59Z";
                    $fullDateFilter = $startDate . ' TO ' . $endDate;
                    $request->query->set('year_month_day_filter', $fullDateFilter);
                }

                return $yearMonthFilter;
            } else if ($this::DATE_FILTER_ON == '+1MONTH') {
                if (isset($yearMonthFilter) && null != $yearMonthFilter && 'all_dates' !== $yearMonthFilter) {
                    list($year, $month, $day) = explode('-', $yearMonthFilter);
                    $nextMonth = (int)$month + 1;
                    if ($nextMonth === 13) {
                        $nextMonth = 1;
                    }
                    $startDate = "{$year}-{$month}-01T00:00:00Z";
                    $endDate = "$year-{$nextMonth}-01T00:00:00Z";
                    $fullDateFilter = $startDate . ' TO ' . $endDate;

                    $request->query->set('year_month_day_filter', $fullDateFilter);
                } elseif ('all_dates' === $yearMonthFilter) {
                    $request->query->set('year_month_day_filter', '1000-01-01T00:00:00Z TO 3000-09-17T23:59:59Z');
                }
            }
        }
    }

    public
    function getYearMonthDates(Request $request): array
    {
        $dateAmount = $this->provider->getFilterOptionsFromSolr($request, $this::DATE_FILTER_ON);

        return $this->transformDateYearToFrontendArray($dateAmount);
    }

    public
    function transformDateYearToFrontendArray($dates): array
    {
        $result = [];
        foreach ($dates as $yearMonth => $amount) {
            if ($this::DATE_FILTER_ON == '+1DAY') {
                $label = substr($yearMonth, 0, 10);
            } else if ($this::DATE_FILTER_ON == '+1MONTH') {
                $label = substr($yearMonth, 0, 7);
            }

            $result[$yearMonth] = [
                'label' => $label . ' (' . $amount . ')',
                'yearMonth' => $yearMonth,
                'amount' => $amount,
            ];
        }

        return $result;
    }

    public
    function getParams($request, $uniqueContentTypes, $dateFilter)
    {
        // Handle that MediaTaxonomy can be "WATER" or "[WATER]" or null
        $mediaTaxonomy = 'null';
        if (\is_array($request->query->get('MediaTaxonomy'))) {
            $mediaTaxonomy = $request->query->get('MediaTaxonomy')[0];
        } elseif (\is_string($request->query->get('MediaTaxonomy'))) {
            $mediaTaxonomy = $request->query->get('MediaTaxonomy');
        }

        $params = [
            'date_filter' => [
                'options' => [
                    'all_dates' => [
                        'name' => 'Alles',
                        'label' => 'Alle datums',
                    ],
                ],
                'current' => $request->query->get('year_month'),
                'default' => 'all_dates',
            ],
            'content_types' => [
                'options' => [
                    'all_files' => [
                        'label_plural' => 'Alle mediabestanden',
                    ],
                ],
                'current' => $request->query->get('class_string'),
                'default' => 'all_files',
            ],
            // NEW ITEMS
            'types' => [
            ],
            'media_taxonomy' => [
                'current' => $mediaTaxonomy,
                'default' => null,
            ],
        ];

        foreach ($this::DEFAULT_FILE_TYPES as $file_type_key => $file_type) {
            $params['content_types']['options'][$file_type_key] = $file_type;
            $params['types'][$file_type_key] = $file_type;
        }

        $paramsExtended = $this->addContentTypesToUserOptions($uniqueContentTypes, $params, $dateFilter);

        return $this->checkIfCurrentExistsAsKey($paramsExtended);
    }

    public
    function addContentTypesToUserOptions(array $uniqueContentTypes, array $params, array $dateFilter)
    {
        foreach ($uniqueContentTypes as $uniqueContentType) {
            // The default categories are always there, and dont need to be added again.
            if (\in_array($uniqueContentType, array_column($this::DEFAULT_FILE_TYPES, 'class_name'))) {
                continue;
            }

            // For filtering of content_types
            $params['content_types']['options'][$uniqueContentType] = [
                'name' => $uniqueContentType,
                'label_plural' => ucfirst($uniqueContentType),
            ];

            // For new items of content_type:
            $params['types'][$uniqueContentType] = [
                'type' => $uniqueContentType,
                'label_singular' => ucfirst($uniqueContentType),
            ];
        }

        foreach ($dateFilter as $yearMonth) {
            $params['date_filter']['options'][$yearMonth['yearMonth']] = [
                'type' => $yearMonth['yearMonth'],
                'label' => $yearMonth['label'],
                'name' => $yearMonth['label'],
            ];
        }

        return $params;
    }

    public
    function checkIfCurrentExistsAsKey(array $paramsExtended): array
    {
        $currentDate = $paramsExtended['date_filter']['current'];

        if (false === \array_key_exists($currentDate, $paramsExtended['date_filter']['options'])) {
            $paramsExtended['date_filter']['current'] = 'all_dates';
        }

        $currentContentType = $paramsExtended['content_types']['current'];
        if (false === \array_key_exists($currentContentType, $paramsExtended['content_types']['options'])) {
            $paramsExtended['content_types']['current'] = null;
        }

        return $paramsExtended;
    }

    public
    function createPaginator($items, $requestSource): SlidingPagination
    {
        $paginator = $this->getPaginator()->paginate(
            $items,
            $requestSource->query->get('page', 1),
            $this::PAGINATOR_LIMIT
        );

        $paginator->amountOfPages = ceil($paginator->getTotalItemCount() / $paginator->getItemNumberPerPage());
        $paginator->showingStart = $paginator->getCurrentPageNumber() * $this::PAGINATOR_LIMIT - $this::PAGINATOR_LIMIT + 1;
        $paginator->showingEnd = $paginator->getCurrentPageNumber() * $this::PAGINATOR_LIMIT;
        if ($paginator->showingEnd > $paginator->getTotalItemCount()) {
            $paginator->showingEnd = $paginator->getTotalItemCount();
        }

        return $paginator;
    }

    /**
     * @deprecated
     */
    public
    function getDatesOfItems($items)
    {
        $dates = [];
        foreach ($items as $item) {
            $yearMonth = $item->getCreatedAt()->format('Y-m-d');
            if (\array_key_exists($yearMonth, $dates)) {
                ++$dates[$yearMonth];
            } else {
                $dates[$yearMonth] = 1;
            }
        }

        return $this->transformDateYearToFrontendArray($dates);
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

            // TODO Enable this when we have proper data
//          if ($read === true || $write === true) {
            $channelAuthorisations[] = $value;
//                $channelAuthorisations[$value ] = [
//                    "read" => $read,
//                    "write" => $write
//                ];
//            }
        }

        return $channelAuthorisations;
    }

    public
    function getContentTypeName($item)
    {
        $contentTypes = array_column($this::DEFAULT_FILE_TYPES, 'class_path');
        $className = $item->getClass();

        if (\in_array($className, $contentTypes)) {
            return $item->getName();
        }
    }

// Update relation of mediaItems
// I want to keep the messages for debugging
    public function manageRelations(Request $request)
    {
        $this->dm = $this->getDoctrineODM()->getManager();
        $messages = [];
        $params = json_decode($request->getContent(), true);
//      "media_id" => "daf99de93f2f3d5e97306bbab4ae5abb"                     REQUIRED, one or many
//      "category_id_target" => "category_2-1"                               REQUIRED, one
//      "category_id_origin" => "3324234"                                    REQUIRED, one

        // Is the user dragging from and to the same folder
        // TODO disable this at client side
        if ($params['category_id_target'] === $params['category_id_origin']) {
            return new JsonResponse('Origin is same as target');
        }

        // get the Taxonomy (Category) with $params["category_id"]
        $taxonomy = null;
        if ($params['category_id_target']) {
            $taxonomy = $this->dm->getRepository(Taxonomy::class)->find($params['category_id_target']);
        }

        // if media id is a string, convert it to an array
        if (true === \is_string($params['media_id'])) {
            $params['media_id'] = [$params['media_id']];
        }

        $mediaItems = $this->dm->createQueryBuilder(File::class)
            ->field('id')->in($params['media_id'])
            ->getQuery()
            ->execute()->toArray();

        $counter = 1;
        foreach ($mediaItems as $mediaItem) {
            $messages[] = '';
            $messages[] = $counter++;

            if ($relations = $mediaItem->getRelation('mediaitem_channelcategory')) {
                $messages[] = 'relation exists?';
            } else {
                $messages[] = 'new relation?';
                $relations = (new Relation())
                    ->setRelationId('mediaitem_channelcategory')
                    ->setRelationType('taxonomy');
            }

            // Check if references already contain this id:
            $relationIDs = $relations->getReferences()->map(function ($item) {
                return $item->getID();
            })->toArray();

            // Remove relation when needed:
            if ('' !== $params['category_id_origin']) {
                $messages[] = 'origin: ' . $params['category_id_origin'];
                $messages[] = 'relationIDs: ' . implode('-', $relationIDs);

                if (\in_array($params['category_id_origin'], $relationIDs)) {
                    $messages[] = 'The origin link will be removed.';
                    $removeThisTaxonomy = $this->dm->getRepository(Taxonomy::class)->find($params['category_id_origin']);
                    $relations->removeReference($removeThisTaxonomy);
                    $messages[] = 'Removed';
                }
            }

            if (\in_array($taxonomy->getID(), $relationIDs)) {
                $messages[] = 'in array';
            } else {
                $messages[] = 'not in array';
                // Add the new taxonomy item
                $relations->addReference($taxonomy);
                $mediaItem->addRelation($relations);
            }

            $this->updateQueueToSolr($mediaItem);
        }

        return new JsonResponse($messages);
    }

    public function updateQueueToSolr($content)
    {
        $queue = $this->queueSubscriber->getQueue();
        $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);
        $this->dm->persist($content);
        $this->dm->flush();
        $this->indexer->setOption('queue.size', 2); // 1 voor het item, 1 voor de commit message
        $this->indexer->execute();
    }

// TODO later make this
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
            /* @var menuItem \Integrated\Bundle\ContentBundle\Document\Channel\Channel */
            foreach ($mediaGalleryMenuResult as $menuItem) {
                $menuItems[$menuItem->getId()] = $channel->getName();
            }
        }

        return $this->render('@IntegratedContent/media/menu.html.twig', [
            'channels' => $channels,
        ]);
    }
}
