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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\ContentBundle\Bulk\DeleteHandler;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryEditFile;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryMenu;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryUploadFile;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\ContentBundle\Services\TaxonomyRelationManager;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/*
 * Goal for the user:
 *  - show files in a coherent manner
 *  - let the user filter / search for specific content
 *  - let the user be able to organise with categories and channels
 *
 * Codewise:
 *  - Media items are ContentType of the sort: File.
 *  - A Media item can be a Video, Image, File(OtherFile), or a custom type. But they are all extended from file.
 *  - To work with database, you are working with the classnames, so: Image, Video, CustomContentType
 *  - To work with url, you work with camelcase, so: image, custom_content_type
 */

class MediaController extends AbstractController
{
    public const PAGINATOR_LIMIT = 40;
    public const DATE_FILTER_ON = '+1MONTH';
    public const NOT_SHOWN_FILETYPES = ['jpg', 'jpeg', 'png', 'tif', 'webp', 'mp4', 'mov', 'avi', 'flv', 'mkv', 'wmv'];
    public const HARD_CODED_CATEGORY = 'MediaTaxonomy';
    public const DEFAULT_FILE_TYPES = [
        'image' => [
            'label_singular' => 'Image',
            'label_plural' => 'Images',
            'solr_name' => 'Image',
            'class_name' => 'Image',
            'class_path' => 'Integrated\Bundle\ContentBundle\Document\Content\Image',
        ],
        'video' => [
            'label_singular' => 'Video',
            'label_plural' => 'Videos',
            'solr_name' => 'Video',
            'class_name' => 'Video',
            'class_path' => 'Integrated\Bundle\ContentBundle\Document\Content\Video',
        ],
        'file' => [
            'label_singular' => 'File',
            'label_plural' => 'Files',
            'solr_name' => 'OtherFile',
            'class_name' => 'File',
            'class_path' => 'Integrated\Bundle\ContentBundle\Document\Content\File',
        ],
    ];
    public const SOLR_ALL_MEDIA_CLASS_STRING = 'File';

    public function __construct(
        private DocumentManager $documentManager,
        private MediaGalleryMenu $mediaGalleryMenu,
        private ContentProvider $provider,
        private TaxonomyRelationManager $taxonomyRelationManager,
        protected AuthorizationCheckerInterface $authorizationChecker,
        private MediaGalleryUploadFile $mediaGalleryUploadFile,
        private MediaGalleryEditFile $mediaGalleryEditFile,
        private ContentRepository $contentRepository,
        private readonly QueryFactoryInterface $queryFactory,
    ) {
    }

    public function index(Request $request): Response
    {
        $data = $this->indexComponent($request);

        return $this->render('@IntegratedContent/media/index.html.twig', [
            ...$data,
        ]);
    }

    public function selectOne(Request $request): Response
    {
        $data = $this->indexComponent($request);

        return $this->render('@IntegratedContent/media/select_one.html.twig', [
            'selected_modus' => 'select_one',
            ...$data,
        ]);
    }

    public function selectMultiple(Request $request): Response
    {
        $data = $this->indexComponent($request);

        return $this->render('@IntegratedContent/media/select_multiple.html.twig', [
            'selected_modus' => 'select_multiple',
            ...$data,
        ]);
    }

    public function indexComponent(Request $request): array
    {
        $contentTypes = $this->getContentTypes($request->query->all('available_contenttypes'));
        $menu = $this->mediaGalleryMenu->createMenu();

        $options = $request->query->all();
        $options['sort'] = 'created';
        $options['contenttypes'] = [];
        foreach ($contentTypes as $contentType) {
            $options['contenttypes'][] = $contentType->getId();
        }
        if (\count($request->query->all('contenttypes'))) {
            $options['contenttypes'] = array_intersect($options['contenttypes'], $request->query->all('contenttypes'));
        }

        $this->setYearMonthFilter($options, $request->query->get('year_month'));

        $client = $this->getSolarium();
        $client->getPlugin('postbigrequest');

        $query = $this->queryFactory->createQuery(IntegratedContent::class, $options);

        $paginator = $this->getPaginator()->paginate(
            [$client, $query->getQuery()],
            $request->query->get('page', 1),
            $request->query->get('limit', 40),
            [PaginatorInterface::SORT_FIELD_PARAMETER_NAME => null]
        );

        $contentTypeFilterOptions = $this->getContentTypeFilterOptions($request);

        $dateFilter = $this->getYearMonthDates($request, $contentTypes);
        $dateFilterOptions = $this->getDateFilterOptions($request, $dateFilter);

        $request = $this->removeIdsFromRequest($request);

        return [
            'paginator' => $paginator,
            'contentTypeSelectOptions' => $this->removeStandardClasses($contentTypes),
            'contentTypeFilterOptions' => $contentTypeFilterOptions,
            'dateFilterOptions' => $dateFilterOptions,
            'menu' => $menu,
            'not_shown_filetypes' => array_map(
                fn ($item) => strtolower($item),
                $this::NOT_SHOWN_FILETYPES
            ),
        ];
    }

    public function editImage(string $id, Request $request, string $format): Response
    {
        $file = $this->documentManager->getRepository(File::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('File not found.');
        }

        return $this->render("@IntegratedContent/media/edit_image{$format}.html.twig", [
            'id' => $id,
            'title' => $file->getTitle(),
            'meta' => json_encode(
                [
                    'mimetype' => $file->getFile()->getMetadata()->getMimeType(),
                    'extension' => $file->getFile()->getMetadata()->getExtension(),
                ]
            ),
            'previous_url' => $request->headers->get('referer'),
            'file_url' => $request->server->get('REQUEST_SCHEME').'://'.$request->server->get('SERVER_NAME').$file->getFile()->getPathName(),
        ]);
    }

    private function removeIdsFromRequest(Request $request): Request
    {
        $request->query->remove('ids');

        return $request;
    }

    private function removeStandardClasses($contentTypeSelectOptions): array
    {
        return array_filter($contentTypeSelectOptions, function ($item) {
            return !\in_array($item->getName(), array_column($this::DEFAULT_FILE_TYPES, 'class_name'));
        });
    }

    // There is a class File -> Image that has a file. The file references to a file on the hard drive.
    public function uploadFile(Request $request)
    {
        try {
            if ($request->get('user_approved_overwrite') === 'true') {
                // Creating a new file, replacing the class Image
                $file = $this->documentManager->getRepository(File::class)->find($request->get('id'));
                $file = $this->mediaGalleryEditFile->replaceImage($request, $file);
            } else {
                if ($request->get('user_approved_overwrite') === 'false') {
                    // Creating a new file, creating a new class Image
                    $file = $this->mediaGalleryEditFile->createCopy($request);
                } else { // new upload
                    // Creating a new file, creating a new class Image
                    $file = $this->mediaGalleryUploadFile->handleUpload($request);
                    $this->taxonomyRelationManager->runSolrQueue();
                }

                $request->attributes->set('media_id', $file->getId());

                $this->taxonomyRelationManager->manageRelations($request);
            }
            // save the relation
            $this->taxonomyRelationManager->runSolrQueue();

            return new JsonResponse(['message' => 'File is uploaded?', 'content' => json_encode($file)]);
        } catch (\Exception $e) {
            return (new JsonResponse(
                ['error' => 'This file is not uploaded. Is this filetype allowed? Is the file too big?']
            ))
                ->setStatusCode(422);
        }
    }

    public function bulkDelete(Request $request, ?DeleteHandler $deleteHandler = null): Response
    {
        $jsonContent = json_decode($request->getContent(), true);

        if (false === $jsonContent['confirmed_by_user']) {
            return $this->getUsedBy($jsonContent['bulkselection']);
        }

        return $this->removeRelations($jsonContent['bulkselection']);
    }

    private function removeRelations(array $bulkselection): Response
    {
        $deletedIds = [];
        $toBeDeletedArray = $this->documentManager->getRepository(Content::class)->findBy(
            ['_id' => ['$in' => $bulkselection]]
        );

        $searchReferenced = new SearchContentReferenced($this->documentManager);
        $deleteHandler = new DeleteHandler($this->documentManager, $searchReferenced, true);
        $deleteHandler->multiExecute($toBeDeletedArray, $bulkselection);

        $this->taxonomyRelationManager->runSolrQueue();

        return new JsonResponse(
            [
                'message' => 'Removed some items',
                'ids' => $deletedIds,
            ]
        );
    }

    private function getUsedBy(array $idSelection): Response
    {
        $usesByTitles = [];
        foreach ($idSelection as $id) {
            $content = $this->contentRepository->find($id);

            if ($content) {
                // get the usedby, is there an easier way?
                $usedByItems = $this->contentRepository
                    ->getUsedBy(new ArrayCollection([$content]), null, null, false)
                    ->getQuery()
                    ->execute();

                /** @var BlockRepository $repository */
                $repository = $this->documentManager
                    ->getRepository(Block::class);

                $usedByBlocks = $repository->getUsedBy(new ArrayCollection([$content]), null, null, false)
                    ->getQuery()
                    ->execute();

                $usedByResult = [];
                if (\count($usedByItems) > 0) {
                    foreach ($usedByItems as $usedByItem) {
                        $usedByResult[] = [
                            'link' => '/admin/content/'.$usedByItem->getId(),
                            'title' => $usedByItem->getTitle(),
                        ];
                    }
                }

                if (\count($usedByBlocks) > 0) {
                    foreach ($usedByBlocks as $usedByBlock) {
                        $usedByResult[] = [
                            'link' => '/admin/block/'.$usedByBlock->getId().'/edit',
                            'title' => $usedByBlock->getTitle(),
                        ];
                    }
                }

                $usesByTitles[] =
                    [
                        'usedBy' => $usedByResult,
                        'id' => $content->getId(),
                        'title' => $content->getTitle(),
                    ];
            }
        }

        if (\count($usesByTitles) > 0) {
            return new JsonResponse([
                'message' => 'There exist some relations. Are you SURE?',
                'used_by' => $usesByTitles,
            ]);
        } else {
            return new JsonResponse(['message' => 'Ok to delete, go for it!']);
        }
    }

    private function getDateFilterOptions(Request $request, array $dateFilter): array
    {
        $currentSelection = $request->query->get('year_month');

        $filter = [
            'options' => [
                'all_dates' => [
                    'name' => 'Alles',
                    'label' => 'All dates',
                ],
            ],
            'default' => 'all_dates',
        ];

        foreach ($dateFilter as $yearMonth) {
            $filter['options'][$yearMonth['yearMonth']] = [
                'type' => $yearMonth['yearMonth'],
                'label' => $yearMonth['label'],
                'name' => $yearMonth['label'],
            ];
        }

        $filter['current'] = \array_key_exists($currentSelection, $filter['options']) ? $currentSelection : 'all_dates';

        return $filter;
    }

    private function getContentTypes(array $filterContentTypes): array
    {
        // TODO: Make sure File and or Files are shown correctly. Not sure if it shows both File and Files due to data.
        $allowedClasses = array_column($this::DEFAULT_FILE_TYPES, 'class_path');
        $allContentTypes = $this->documentManager->getRepository(ContentType::class)->findBy([], ['name' => 1]);

        $result = [];
        foreach ($allContentTypes as $contentType) {
            if (!$this->authorizationChecker->isGranted(PermissionInterface::WRITE, $contentType)) {
                continue;
            }

            if (\count($filterContentTypes) && !\in_array($contentType->getId(), $filterContentTypes)) {
                continue;
            }

            $className = $contentType->getClass();
            if (\in_array($className, $allowedClasses)) {
                $result[] = $contentType;
            }
        }

        return $result;
    }

    /*              specific day:
    *               --------xx                      xx
    *               2022-09-17T00:00:00Z TO 2022-09-17T23:59:59Z
    *               specific month:
    *               -----xx                      xx
    *               2022-09-01T00:00:00Z TO 2022-10-01T00:00:00Z
    */
    private function setYearMonthFilter(array &$options, ?string $yearMonthFilter)
    {
        if (null === $yearMonthFilter || 'all_dates' === $yearMonthFilter) {
            return;
        }

        list($year, $month, $day) = explode('-', $yearMonthFilter);
        $startDate = "{$year}-{$month}-01T00:00:00Z";
        $nextMonth = (int) $month + 1;
        if ($nextMonth === 13) {
            $nextMonth = 1;
            $year = (int) $year + 1;
        }
        $nextMonth = str_pad($nextMonth, 2, '0', \STR_PAD_LEFT);
        $endDate = "$year-{$nextMonth}-01T00:00:00Z";
        $fullDateFilter = $startDate.' TO '.$endDate;

        $options['created'] = [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    private function getYearMonthDates(Request $request, $contentTypeSelectOptions): array
    {
        $dateAmount = $this->provider->getFilterOptionsFromSolr(
            $request,
            $this::DATE_FILTER_ON,
            $contentTypeSelectOptions
        );

        return $this->transformDateYearToFrontendArray($dateAmount);
    }

    private function transformDateYearToFrontendArray($dates): array
    {
        $result = [];
        foreach ($dates as $yearMonth => $amount) {
            $label = '';
            if ($this::DATE_FILTER_ON == '+1MONTH') {
                $label = substr($yearMonth, 0, 7);
            }

            $result[$yearMonth] = [
                'label' => $label.' ('.$amount.')',
                'yearMonth' => $yearMonth,
                'amount' => $amount,
            ];
        }

        return $result;
    }

    private function getContentTypeFilterOptions(Request $request): array
    {
        $current = 'all_files';
        if ($contenttypes = $request->query->all('contenttypes')) {
            if (\count($contenttypes)) {
                $current = $contenttypes[0];
            }
        }

        $filter = [
            'options' => [
                'all_files' => [
                    'id' => null,
                    'name' => 'Alle mediafiles',
                ],
            ],
            'current' => $current,
            'default' => 'All mediafiles',
        ];

        $contentTypes = array_column($this::DEFAULT_FILE_TYPES, 'class_path');
        $allContentTypes = $this->documentManager->getRepository(ContentType::class)->findBy([], ['name' => 1]);

        $availableContenttypes = $request->query->all('available_contenttypes');
        if (\count($availableContenttypes)) {
            $allContentTypes = array_filter($allContentTypes, function ($item) use ($availableContenttypes) {
                return \in_array($item->getId(), $availableContenttypes) == true;
            });
        }

        foreach ($allContentTypes as $contentType) {
            if (!$this->authorizationChecker->isGranted(PermissionInterface::WRITE, $contentType)) {
                continue;
            }

            $className = $contentType->getClass();

            if (\in_array($className, $contentTypes)) {
                $filter['options'][$contentType->getId()] = $contentType;
            }
        }

        return $filter;
    }

    public function manageRelations(Request $request): Response
    {
        $this->taxonomyRelationManager->manageRelations($request);

        return new JsonResponse(['message' => 'Ok']);
    }
}
