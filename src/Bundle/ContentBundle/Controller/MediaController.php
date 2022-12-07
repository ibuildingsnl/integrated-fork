<?php

/*
 *
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\ContentBundle\Services\MediaGalleryMenu;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Integrated\Bundle\ContentBundle\Services\TaxonomyRelationManager;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
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
    public const DATE_FILTER_ON = '+1MONTH'; // 1DAY or 1MONTH
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
        protected AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    // TODO: Either work with ID`s or do some checks that a category has a unique name
    public function index(Request $requestSource): Response
    {
        $contentTypeSelectOptions = $this->getContentTypes();

        $requestCopy = clone $requestSource;

        // Todo: Update this code when the contentprovides is updated
        $givenContentType = $requestCopy->get('contenttypes');
        if (\is_array($givenContentType) && \count($givenContentType) > 0) {
            $givenContentType = $givenContentType[0];
        }
        if ($givenContentType !== 'all_files' && $givenContentType !== null) {
            $requestCopy->query->set('contenttypes', [$givenContentType]);
        } else {
            $requestSource->query->set('contenttypes', 'all_files');
        }

        $requestCopy = $this->setAndGetMediaType($requestCopy, $contentTypeSelectOptions);

        $menu = $this->mediaGalleryMenu->createMenu();

        $this->setYearMonthFilter($requestCopy);

        $items = $this->provider->getContentFromSolr($requestCopy, 2000);

        $selectedMediaTaxonomy = $this->getSelectedMediaTaxonomy($requestCopy);

        $contentTypeFilterOptions = $this->getContentTypeFilterOptions($requestSource);

        $dateFilter = $this->getYearMonthDates($requestCopy, $contentTypeSelectOptions);
        $dateFilterOptions = $this->getDateFilterOptions($requestCopy, $dateFilter);

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'paginator' => $this->createPaginator($items, $requestSource),
            'items' => $items,
            'contentTypeSelectOptions' => $contentTypeSelectOptions,
            'contentTypeFilterOptions' => $contentTypeFilterOptions,
            'dateFilterOptions' => $dateFilterOptions,
            'selectedMediaTaxonomy' => $selectedMediaTaxonomy,
            'menu' => $menu,
            'not_shown_filetypes' => array_map(
                fn ($item) => strtolower($item),
                $this::NOT_SHOWN_FILETYPES
            ),
        ]);
    }

    public function index_component(Request $requestSource): Response
    {
        $contentTypeSelectOptions = $this->getContentTypes();

        $requestCopy = clone $requestSource;

        // Todo: Update this code when the contentprovides is updated
        $givenContentType = $requestCopy->get('contenttypes');
        if (\is_array($givenContentType) && \count($givenContentType) > 0) {
            $givenContentType = $givenContentType[0];
        }
        if ($givenContentType !== 'all_files' && $givenContentType !== null) {
            $requestCopy->query->set('contenttypes', [$givenContentType]);
        } else {
            $requestSource->query->set('contenttypes', 'all_files');
        }

        $requestCopy = $this->setAndGetMediaType($requestCopy, $contentTypeSelectOptions);

        $menu = $this->mediaGalleryMenu->createMenu();

        $this->setYearMonthFilter($requestCopy);

        $items = $this->provider->getContentFromSolr($requestCopy, 2000);

        $selectedMediaTaxonomy = $this->getSelectedMediaTaxonomy($requestCopy);

        $contentTypeFilterOptions = $this->getContentTypeFilterOptions($requestSource);

        $dateFilter = $this->getYearMonthDates($requestCopy, $contentTypeSelectOptions);
        $dateFilterOptions = $this->getDateFilterOptions($requestCopy, $dateFilter);

        return $this->render('@IntegratedContent/media/index_component.html.twig', [
            'paginator' => $this->createPaginator($items, $requestSource),
            'items' => $items,
            'contentTypeSelectOptions' => $contentTypeSelectOptions,
            'contentTypeFilterOptions' => $contentTypeFilterOptions,
            'dateFilterOptions' => $dateFilterOptions,
            'selectedMediaTaxonomy' => $selectedMediaTaxonomy,
            'menu' => $menu,
            'not_shown_filetypes' => array_map(
                fn ($item) => strtolower($item),
                $this::NOT_SHOWN_FILETYPES
            ),
        ]);
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

    private function setAndGetMediaType(Request $request, $contentTypeSelectOptions): Request
    {
        /** we want to keep two things separate:
         * - what the user asks for
         * - what we query
         * because with the user selection 'Alle Mediafiles' we want to query for the class: File.
         * but when the user clicks on 'Files' we want to query on 'OtherFile'.
         */
        $contentType = $request->query->get('contenttypes');
        if (null === $contentType || 'all_files' === $contentType) {
            $contentTypes = [];
            foreach ($contentTypeSelectOptions as $contentTypeSelectOption) {
                $contentTypes[] = $contentTypeSelectOption->getId();
            }
            $request->query->set('contenttypes', $contentTypes);
        }

        if (null !== $request->query->get('MediaTaxonomy')) {
            $request->query->set('MediaTaxonomy', [$request->query->get('MediaTaxonomy')]);
            $request->query->set('MediaTaxonomy[]', [$request->query->get('MediaTaxonomy')]);
        }

        return $request;
    }

    private function getContentTypes(): array
    {
        // TODO: Make sure File and or Files are shown correctly. Not sure if it shows both File and Files due to data.
        $contentTypes = array_column($this::DEFAULT_FILE_TYPES, 'class_path');
        $allContentTypes = $this->documentManager->getRepository(ContentType::class)->findAll();

        $result = [];
        foreach ($allContentTypes as $contentType) {
            if (!$this->authorizationChecker->isGranted(PermissionInterface::WRITE, $contentType)) {
                continue;
            }

            $className = $contentType->getClass();
            if (\in_array($className, $contentTypes)) {
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
    private function setYearMonthFilter($request)
    {
        $yearMonthFilter = $request->query->get('year_month');

        if (null === $yearMonthFilter) {
            return;
        } elseif ('all_dates' === $yearMonthFilter) {
            $request->query->set('year_month_day_filter', '1000-01-01T00:00:00Z TO 3000-09-17T23:59:59Z');
        } else {
            if ($this::DATE_FILTER_ON == '+1DAY') {
                if (null != $yearMonthFilter && 'all_dates' !== $yearMonthFilter) {
                    list($year, $month, $day) = explode('-', $yearMonthFilter);
                    $startDate = "{$year}-{$month}-{$day}T00:00:00Z";
                    $endDate = "$year-{$month}-{$day}T23:59:59Z";
                    $fullDateFilter = $startDate.' TO '.$endDate;
                    $request->query->set('year_month_day_filter', $fullDateFilter);
                }

                return $yearMonthFilter;
            } elseif ($this::DATE_FILTER_ON == '+1MONTH') {
                if (null != $yearMonthFilter && 'all_dates' !== $yearMonthFilter) {
                    list($year, $month, $day) = explode('-', $yearMonthFilter);
                    $nextMonth = (int) $month + 1;
                    if ($nextMonth === 13) {
                        $nextMonth = 1;
                    }
                    $startDate = "{$year}-{$month}-01T00:00:00Z";
                    $endDate = "$year-{$nextMonth}-01T00:00:00Z";
                    $fullDateFilter = $startDate.' TO '.$endDate;

                    $request->query->set('year_month_day_filter', $fullDateFilter);
                } elseif ('all_dates' === $yearMonthFilter) {
                    $request->query->set('year_month_day_filter', '1000-01-01T00:00:00Z TO 3000-09-17T23:59:59Z');
                }
            }
        }
    }

    private function getYearMonthDates(Request $request, $contentTypeSelectOptions): array
    {
        $dateAmount = $this->provider->getFilterOptionsFromSolr($request, $this::DATE_FILTER_ON, $contentTypeSelectOptions);

        return $this->transformDateYearToFrontendArray($dateAmount);
    }

    private function transformDateYearToFrontendArray($dates): array
    {
        $result = [];
        foreach ($dates as $yearMonth => $amount) {
            $label = '';
            if ($this::DATE_FILTER_ON == '+1DAY') {
                $label = substr($yearMonth, 0, 10);
            } elseif ($this::DATE_FILTER_ON == '+1MONTH') {
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
        $filter = [
            'options' => [
                'all_files' => [
                    'id' => 'all_files',
                    'name' => 'Alle mediafiles',
                ],
            ],
            'current' => $request->query->get('contenttypes'),
            'default' => 'All mediafiles',
        ];

        $contentTypes = array_column($this::DEFAULT_FILE_TYPES, 'class_path');
        $allContentTypes = $this->documentManager->getRepository(ContentType::class)->findAll();

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

    private function getSelectedMediaTaxonomy(Request $request): array
    {
        // Handle that MediaTaxonomy can be "WATER" or "[WATER]" or null
        $mediaTaxonomy = 'null';
        if (\is_array($request->query->get('MediaTaxonomy'))) {
            $mediaTaxonomy = $request->query->get('MediaTaxonomy')[0];
        } elseif (\is_string($request->query->get('MediaTaxonomy'))) {
            $mediaTaxonomy = $request->query->get('MediaTaxonomy');
        }

        return [
            'current' => $mediaTaxonomy,
            'default' => null,
        ];
    }

    private function createPaginator(array $items, Request $requestSource): SlidingPagination
    {
        $paginator = $this->getPaginator()->paginate(
            $items,
            $requestSource->query->get('page', 1),
            $this::PAGINATOR_LIMIT
        );

        $showingEnd = $paginator->getCurrentPageNumber() * $this::PAGINATOR_LIMIT;
        if ($showingEnd > $paginator->getTotalItemCount()) {
            $showingEnd = $paginator->getTotalItemCount();
        }
        $paginator->setCustomParameters([
            'amountOfPages' => ceil($paginator->getTotalItemCount() / $paginator->getItemNumberPerPage()),
            'showingStart' => $paginator->getCurrentPageNumber() * $this::PAGINATOR_LIMIT - $this::PAGINATOR_LIMIT + 1,
            'showingEnd' => $showingEnd,
        ]);

        return $paginator;
    }

    public function manageRelations(Request $request): Response
    {
        return $this->taxonomyRelationManager->manageRelations($request);
    }
}
