<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\IntegratedBundle\Controller\PaginationQueryTrait;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Form\Type\PageCopyType;
use Integrated\Bundle\PageBundle\Form\Type\PageFilterType;
use Integrated\Bundle\PageBundle\Form\Type\PageType;
use Integrated\Bundle\PageBundle\Services\PageCopy\PageCopyRequestFactory;
use Integrated\Bundle\PageBundle\Services\PageCopyService;
use Integrated\Bundle\PageBundle\Services\RouteCache;
use Knp\Component\Pager\PaginatorInterface;
use MongoDB\BSON\Regex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\UriSigner;

class PageController extends AbstractController
{
    use PaginationQueryTrait;

    private const PREVIEW_LINK_TTL_SECONDS = 86400;
    private const PREVIEW_EXPIRES_PARAM = 'preview_expires';
    private const CHANNEL_NONE_VALUE = '__none__';

    private DocumentManager $documentManager;
    private PaginatorInterface $paginator;
    private PageCopyService $pageCopyService;
    private PageCopyRequestFactory $pageCopyRequestFactory;
    private RouteCache $routeCache;
    private UriSigner $uriSigner;
    /** @var array<int, string>|null */
    private ?array $allExistingWebsiteChannelIds = null;

    public function __construct(
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        PageCopyService $pageCopyService,
        PageCopyRequestFactory $pageCopyRequestFactory,
        RouteCache $routeCache,
        UriSigner $uriSigner,
    ) {
        $this->documentManager = $documentManager;
        $this->paginator = $paginator;
        $this->pageCopyService = $pageCopyService;
        $this->pageCopyRequestFactory = $pageCopyRequestFactory;
        $this->routeCache = $routeCache;
        $this->uriSigner = $uriSigner;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $sessionFilterData = $this->normalizePageFilterData($request->getSession()->get('page_filterform_data', []));
        $requestFilterData = $this->normalizePageFilterData($request->query->all('page_filter'));
        if ($requestFilterData !== []) {
            $request->query->set('page_filter', $requestFilterData);
        }

        $activeFilterData = $requestFilterData !== [] ? $requestFilterData : $sessionFilterData;
        $filterCounts = $this->getPageFilterCounts($activeFilterData);

        $filterForm = $this->createForm(
            PageFilterType::class,
            $sessionFilterData,
            [
                'method' => 'GET',
                'action' => $this->generateUrl('integrated_page_page_index'),
                'page_type_counts' => $filterCounts['page_type_counts'],
                'status_counts' => $filterCounts['status_counts'],
                'channel_choices' => $filterCounts['channel_choices'],
            ]
        );
        $filterForm->handleRequest($request);

        $pageTypes = $this->sanitizePageTypes($filterForm->get('pagetype')->getData());
        $class = $this->resolvePageClass($pageTypes);
        $selectedChannels = $this->sanitizeChannels($activeFilterData['channel'] ?? []);

        $builder = $this->documentManager->createQueryBuilder($class);

        $this->displayPathErrors($builder);

        $this->applySearchFilter($builder, (string) ($filterForm->get('q')->getData() ?? ''));
        $this->applyChannelFilter($builder, $this->sanitizeChannels($filterForm->get('channel')->getData()));
        $this->applyStatusFilter($builder, $this->sanitizeStatuses($filterForm->get('status')->getData()), $class);

        $builder->sort('path', 1);
        $builder->sort('channel.$id', 1);

        if ($filterForm->isSubmitted()) {
            $request->getSession()->set('page_filterform_data', $filterForm->getData());
        }

        $pagination = $this->paginator->paginate(
            $builder,
            $this->getPositiveIntQueryParameter($request, 'page', 1),
            25
        );

        $response = $this->render('@IntegratedPage/page/index.html.twig', [
            'pages' => $pagination,
            'filterForm' => $filterForm,
            'lastPage' => $this->getLastEditPage($request->getSession()),
            'previewLinks' => $this->buildPreviewLinks($pagination, $request),
            'orphanChannelPageIds' => $this->getOrphanChannelPageIds($pagination),
            'activeFilterData' => $activeFilterData,
            'noneFilterActive' => \in_array(self::CHANNEL_NONE_VALUE, $selectedChannels, true),
        ]);

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePageFilterData(mixed $data): array
    {
        if (!\is_array($data)) {
            return [];
        }

        foreach (['pagetype', 'status', 'channel'] as $field) {
            $data[$field] = $this->normalizeMultiSelectFilterValue($data[$field] ?? null);
        }

        return $data;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeMultiSelectFilterValue(mixed $value): array
    {
        if (\is_array($value)) {
            $values = $value;
        } elseif (\is_scalar($value) && (string) $value !== '') {
            $values = [(string) $value];
        } else {
            $values = [];
        }

        $values = array_values(array_filter($values, static fn ($item): bool => \is_scalar($item) && (string) $item !== ''));

        return array_map(static fn ($item): string => (string) $item, $values);
    }

    /**
     * @return string[]
     */
    private function sanitizePageTypes(mixed $pageTypes): array
    {
        return array_values(array_unique(array_filter(
            $this->normalizeMultiSelectFilterValue($pageTypes),
            static fn (string $value): bool => \in_array($value, ['page', 'contenttype'], true)
        )));
    }

    /**
     * @return string[]
     */
    private function sanitizeStatuses(mixed $statuses): array
    {
        return array_values(array_unique(array_filter(
            $this->normalizeMultiSelectFilterValue($statuses),
            static fn (string $value): bool => \in_array($value, ['published', 'draft'], true)
        )));
    }

    /**
     * @return string[]
     */
    private function sanitizeChannels(mixed $channels): array
    {
        return array_values(array_unique($this->normalizeMultiSelectFilterValue($channels)));
    }

    private function applySearchFilter(Builder $builder, string $query): void
    {
        $query = trim($query);
        if ($query === '') {
            return;
        }

        $escapedQuery = preg_quote($query, '/');
        $searchExpr = $builder->expr();
        $searchExpr->addOr($builder->expr()->field('title')->equals(new Regex($escapedQuery, 'i')));
        $searchExpr->addOr($builder->expr()->field('path')->equals(new Regex($escapedQuery, 'i')));
        $builder->addAnd($searchExpr);
    }

    /**
     * @param string[] $channels
     */
    private function applyChannelFilter(Builder $builder, array $channels): void
    {
        if ($channels === []) {
            return;
        }

        $includeNone = \in_array(self::CHANNEL_NONE_VALUE, $channels, true);
        $selectedChannelIds = array_values(array_filter($channels, static fn (string $channel): bool => $channel !== self::CHANNEL_NONE_VALUE));

        if (!$includeNone) {
            $builder->field('channel.$id')->in($selectedChannelIds);

            return;
        }

        $channelExpr = $builder->expr();
        if ($selectedChannelIds !== []) {
            $channelExpr->addOr($builder->expr()->field('channel.$id')->in($selectedChannelIds));
        }

        $channelExpr->addOr($builder->expr()->field('channel.$id')->notIn($this->getAllExistingWebsiteChannelIds()));
        $builder->addAnd($channelExpr);
    }

    /**
     * @param string[] $pageTypes
     */
    private function applyPageTypeFilter(Builder $builder, array $pageTypes, string $class): void
    {
        if ($pageTypes === [] || \count($pageTypes) > 1) {
            return;
        }

        if ($class === Page::class && $pageTypes === ['contenttype']) {
            $builder->field('id')->equals('__no_results__');

            return;
        }

        if ($class === ContentTypePage::class && $pageTypes === ['page']) {
            $builder->field('id')->equals('__no_results__');

            return;
        }

        if ($class !== AbstractPage::class) {
            return;
        }

        if ($pageTypes === ['page']) {
            // Legacy pages can have null class discriminator.
            $builder->field('class')->in(['Page', null]);

            return;
        }

        if ($pageTypes === ['contenttype']) {
            $builder->field('class')->equals('ContentTypePage');
        }
    }

    /**
     * @param string[] $statuses
     */
    private function applyStatusFilter(Builder $builder, array $statuses, string $class): void
    {
        $filterPublished = \in_array('published', $statuses, true);
        $filterDraft = \in_array('draft', $statuses, true);

        if ($filterPublished === $filterDraft) {
            return;
        }

        if ($filterPublished) {
            if ($class === Page::class) {
                $builder->field('disabled')->equals(false);
            } elseif ($class === AbstractPage::class) {
                // ContentTypePage has no "disabled" field and is always published.
                $builder->field('disabled')->notEqual(true);
            }

            return;
        }

        if ($class === ContentTypePage::class) {
            $builder->field('id')->equals('__no_results__');

            return;
        }

        $builder->field('disabled')->equals(true);
    }

    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $page = new Page();

        $form = $this->createCreateForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_page_page_index');
            }

            if ($form->isValid()) {
                $this->documentManager->persist($page);
                $this->documentManager->flush();

                $this->routeCache->clear();

                $this->addFlash('success', \sprintf('Page "%s" has been created', $page->getTitle()));

                $this->setLastEditPage($request->getSession(), $page);

                if ($request->query->get('returnUrl')) {
                    return $this->redirect($request->query->get('returnUrl'));
                }

                return $this->redirectToRoute('integrated_page_page_index');
            }
        }

        return $this->render('@IntegratedPage/page/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Request $request, Page $page): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createEditForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_page_page_index');
            }

            if ($form->isValid()) {
                $this->documentManager->flush();

                $this->routeCache->clear();

                $this->addFlash('success', \sprintf('Page "%s" has been updated', $page->getTitle()));

                $this->setLastEditPage($request->getSession(), $page);

                return $this->redirectToRoute('integrated_page_page_index');
            }
        }

        return $this->render('@IntegratedPage/page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    public function delete(Request $request, Page $page): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($page->isLocked()) {
            throw $this->createNotFoundException(\sprintf('Page "%s" is locked.', $page->getId()));
        }

        $form = $this->createDeleteForm($page->getId());
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_page_page_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->remove($page);
            $this->documentManager->flush();

            $this->routeCache->clear();

            $this->addFlash('success', 'Page deleted');

            return $this->redirectToRoute('integrated_page_page_index');
        }

        return $this->render('@IntegratedPage/page/delete.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    public function copy(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $formData = $request->request->all('page_copy');
        if (!\is_array($formData) || $formData === []) {
            $formData = $request->query->all('page_copy');
        }

        $targetChannel = $formData['targetChannel'] ?? null;
        $sourceChannel = $formData['sourceChannel'] ?? null;

        $form = $this->createForm(
            PageCopyType::class,
            [
                'sourceChannel' => $sourceChannel,
                'targetChannel' => $targetChannel,
            ],
            [
                'sourceChannel' => $sourceChannel,
                'targetChannel' => $targetChannel,
                'action' => $this->generateUrl('integrated_page_page_copy'),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['action'] != 'refresh') {
                try {
                    $requestModel = $this->pageCopyRequestFactory->createFromFormData($data);
                    $this->pageCopyService->copyPages($requestModel);
                } catch (\InvalidArgumentException $exception) {
                    $form->addError(new FormError($exception->getMessage()));
                    $this->addFlash('warning', $exception->getMessage());

                    return $this->render('@IntegratedPage/page/copy.html.twig', [
                        'form' => $form,
                    ]);
                }

                $this->addFlash('success', 'Pages copied');

                return $this->redirectToRoute('integrated_page_page_index');
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->flashUniqueFormErrorsAsWarnings($form);
        }

        return $this->render('@IntegratedPage/page/copy.html.twig', [
            'form' => $form,
        ]);
    }

    private function flashUniqueFormErrorsAsWarnings(FormInterface $form): void
    {
        $messages = [];

        foreach ($form->getErrors(true, true) as $error) {
            $message = trim((string) $error->getMessage());
            if ($message === '') {
                continue;
            }

            $messages[$message] = true;
        }

        foreach (array_keys($messages) as $message) {
            $this->addFlash('warning', $message);
        }
    }

    public function deleteWithoutChannel(Request $request, string $id): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_without_channel_page_'.$id, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $page = $this->documentManager->getRepository(AbstractPage::class)->find($id);
        if (!$page instanceof AbstractPage) {
            throw $this->createNotFoundException();
        }

        if (!$this->isPageWithoutResolvableChannel($page)) {
            $this->addFlash('warning', 'This action is only allowed for pages without channel.');

            return $this->redirectToRoute('integrated_page_page_index');
        }

        $this->documentManager->remove($page);
        $this->documentManager->flush();

        $this->routeCache->clear();
        $this->addFlash('success', 'Page without channel deleted');

        return $this->redirectToRoute('integrated_page_page_index');
    }

    public function deleteWithoutChannelBulk(Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_without_channel_pages_bulk', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $filterData = $this->normalizePageFilterData($request->request->all('page_filter'));
        if ($filterData === []) {
            $filterData = $this->normalizePageFilterData($request->getSession()->get('page_filterform_data', []));
        }

        $pageTypes = $this->sanitizePageTypes($filterData['pagetype'] ?? []);
        $class = $this->resolvePageClass($pageTypes);

        $builder = $this->documentManager->createQueryBuilder($class);
        $this->applySearchFilter($builder, (string) ($filterData['q'] ?? ''));

        $channels = $this->sanitizeChannels($filterData['channel'] ?? []);
        if (!\in_array(self::CHANNEL_NONE_VALUE, $channels, true)) {
            $channels[] = self::CHANNEL_NONE_VALUE;
        }

        $this->applyChannelFilter($builder, $channels);
        $this->applyStatusFilter($builder, $this->sanitizeStatuses($filterData['status'] ?? []), $class);

        $deletedCount = 0;
        $matches = $builder->getQuery()->execute();
        if (!is_iterable($matches)) {
            $matches = [];
        }

        foreach ($matches as $page) {
            if (!$page instanceof AbstractPage) {
                continue;
            }

            if (!$this->isPageWithoutResolvableChannel($page)) {
                continue;
            }

            $this->documentManager->remove($page);
            ++$deletedCount;
        }

        if ($deletedCount > 0) {
            $this->documentManager->flush();
            $this->routeCache->clear();
            $this->addFlash('success', \sprintf('%d pages without channel deleted', $deletedCount));
        } else {
            $this->addFlash('warning', 'No pages without channel found for current filter.');
        }

        return $this->redirectToRoute('integrated_page_page_index', ['page_filter' => $filterData]);
    }

    private function createCreateForm(Page $page): FormInterface
    {
        $form = $this->createForm(PageType::class, $page, [
            'action' => $this->generateUrl('integrated_page_page_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    /**
     * @param string[] $pageTypes
     */
    private function resolvePageClass(array $pageTypes): string
    {
        if ($pageTypes === ['page']) {
            return Page::class;
        }

        if ($pageTypes === ['contenttype']) {
            return ContentTypePage::class;
        }

        return AbstractPage::class;
    }

    private function createEditForm(Page $page): FormInterface
    {
        $form = $this->createForm(PageType::class, $page, [
            'action' => $this->generateUrl('integrated_page_page_edit', ['id' => $page->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    private function createDeleteForm(string $id): FormInterface
    {
        $builder = $this->createFormBuilder();

        $builder->setAction($this->generateUrl('integrated_page_page_delete', ['id' => $id]));
        $builder->setMethod(Request::METHOD_DELETE);
        $builder->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $builder->getForm();
    }

    private function displayPathErrors(Builder $builder): void
    {
        $paths = [];
        $matches = $builder->getQuery()->execute();
        if (!is_iterable($matches)) {
            $matches = [];
        }

        foreach ($matches as $item) {
            if (!$item instanceof ContentTypePage) {
                continue;
            }

            $settings = $item->getControllerService().$item->getLayout();
            $channelId = $this->resolveChannelId($item->getChannel());
            if (null === $channelId) {
                continue;
            }

            $key = $channelId.'-'.$item->getPath();
            if (isset($paths[$key]) && $paths[$key] != $settings) {
                $this->addFlash('danger', 'Path '.$item->getPath().' is used multiple times with different settings. Only one will be used');
                continue;
            }

            $paths[$key] = $settings;
        }
    }

    private function setLastEditPage(SessionInterface $session, Page $page): void
    {
        $session->set('page_lastedit_id', $page->getId());
    }

    private function getLastEditPage(SessionInterface $session): ?Page
    {
        if ($pageId = $session->get('page_lastedit_id')) {
            return $this->documentManager->getRepository(Page::class)->find($pageId);
        }

        return null;
    }

    /**
     * @param iterable<mixed> $pages
     *
     * @return array<string, string>
     */
    private function buildPreviewLinks(iterable $pages, Request $request): array
    {
        $links = [];
        $expires = time() + self::PREVIEW_LINK_TTL_SECONDS;

        foreach ($pages as $page) {
            if (!$page instanceof Page || !$page->isDisabled()) {
                continue;
            }

            $id = (string) $page->getId();
            if ($id === '') {
                continue;
            }

            $url = $this->buildAbsolutePageUrl($page, $request, [
                self::PREVIEW_EXPIRES_PARAM => $expires,
            ]);

            $links[$id] = $this->uriSigner->sign($url);
        }

        return $links;
    }

    /**
     * @param array<string, scalar> $query
     */
    private function buildAbsolutePageUrl(Page $page, Request $request, array $query = []): string
    {
        $host = (string) ($page->getDomain() ?: $request->getHost());
        $scheme = $request->getScheme();
        $path = (string) $page->getPath();
        $queryString = http_build_query($query, '', '&', \PHP_QUERY_RFC3986);

        return $scheme.'://'.$host.$path.($queryString !== '' ? '?'.$queryString : '');
    }

    private function isPageWithoutResolvableChannel(AbstractPage $page): bool
    {
        $channel = $page->getChannel();
        $channelId = $this->resolveChannelId($channel);
        if (null === $channelId) {
            return true;
        }

        return !$this->isWebsiteChannelId($channelId);
    }

    /**
     * @param iterable<mixed> $pages
     *
     * @return array<string, bool>
     */
    private function getOrphanChannelPageIds(iterable $pages): array
    {
        $orphanPageIds = [];
        $channelIdsByPageId = [];

        foreach ($pages as $page) {
            if (!$page instanceof AbstractPage) {
                continue;
            }

            $pageId = (string) $page->getId();
            if ($pageId === '') {
                continue;
            }

            $channelId = $this->resolveChannelId($page->getChannel());
            if (null === $channelId) {
                $orphanPageIds[$pageId] = true;

                continue;
            }

            $channelIdsByPageId[$pageId] = $channelId;
        }

        $knownWebsiteChannelIds = array_fill_keys($this->getAllExistingWebsiteChannelIds(), true);

        foreach ($channelIdsByPageId as $pageId => $channelId) {
            if (!isset($knownWebsiteChannelIds[$channelId])) {
                $orphanPageIds[$pageId] = true;
            }
        }

        return $orphanPageIds;
    }

    /**
     * @param array<string, mixed> $filterData
     *
     * @return array{
     *     page_type_counts: array{page: int, contenttype: int},
     *     status_counts: array{published: int, draft: int},
     *     channel_choices: array<string, string>
     * }
     */
    private function getPageFilterCounts(array $filterData): array
    {
        $query = (string) ($filterData['q'] ?? '');
        $pageTypes = $this->sanitizePageTypes($filterData['pagetype'] ?? []);
        $statuses = $this->sanitizeStatuses($filterData['status'] ?? []);
        $selectedChannels = $this->sanitizeChannels($filterData['channel'] ?? []);

        $pageBuilder = $this->documentManager->createQueryBuilder(Page::class);
        $this->applySearchFilter($pageBuilder, $query);
        $this->applyChannelFilter($pageBuilder, $selectedChannels);
        $this->applyStatusFilter($pageBuilder, $statuses, Page::class);

        $contentTypeBuilder = $this->documentManager->createQueryBuilder(ContentTypePage::class);
        $this->applySearchFilter($contentTypeBuilder, $query);
        $this->applyChannelFilter($contentTypeBuilder, $selectedChannels);
        $this->applyStatusFilter($contentTypeBuilder, $statuses, ContentTypePage::class);

        $pageTypeCounts = [
            'page' => $this->normalizeCountValue($pageBuilder->count()->getQuery()->execute()),
            'contenttype' => $this->normalizeCountValue($contentTypeBuilder->count()->getQuery()->execute()),
        ];

        $publishedBuilder = $this->documentManager->createQueryBuilder(AbstractPage::class);
        $this->applySearchFilter($publishedBuilder, $query);
        $this->applyChannelFilter($publishedBuilder, $selectedChannels);
        $this->applyPageTypeFilter($publishedBuilder, $pageTypes, AbstractPage::class);
        $this->applyStatusFilter($publishedBuilder, ['published'], AbstractPage::class);

        $draftBuilder = $this->documentManager->createQueryBuilder(AbstractPage::class);
        $this->applySearchFilter($draftBuilder, $query);
        $this->applyChannelFilter($draftBuilder, $selectedChannels);
        $this->applyPageTypeFilter($draftBuilder, $pageTypes, AbstractPage::class);
        $this->applyStatusFilter($draftBuilder, ['draft'], AbstractPage::class);

        $statusCounts = [
            'published' => $this->normalizeCountValue($publishedBuilder->count()->getQuery()->execute()),
            'draft' => $this->normalizeCountValue($draftBuilder->count()->getQuery()->execute()),
        ];

        $channelChoices = [];
        $channels = $this->documentManager->getRepository(Channel::class)->findBy(['type.$id' => 'website']);

        $noneBuilder = $this->documentManager->createQueryBuilder(AbstractPage::class);
        $this->applySearchFilter($noneBuilder, $query);
        $this->applyPageTypeFilter($noneBuilder, $pageTypes, AbstractPage::class);
        $this->applyStatusFilter($noneBuilder, $statuses, AbstractPage::class);
        $this->applyChannelFilter($noneBuilder, [self::CHANNEL_NONE_VALUE]);
        $noneCount = $this->normalizeCountValue($noneBuilder->count()->getQuery()->execute());
        if (
            $noneCount > 0
            || \in_array(self::CHANNEL_NONE_VALUE, $selectedChannels, true)
        ) {
            $channelChoices[$this->formatFacetLabel('None', $noneCount)] = self::CHANNEL_NONE_VALUE;
        }

        foreach ($channels as $channel) {
            $channelId = $this->resolveChannelId($channel);
            if (null === $channelId) {
                continue;
            }

            $channelBuilder = $this->documentManager->createQueryBuilder(AbstractPage::class);
            $this->applySearchFilter($channelBuilder, $query);
            $this->applyPageTypeFilter($channelBuilder, $pageTypes, AbstractPage::class);
            $this->applyStatusFilter($channelBuilder, $statuses, AbstractPage::class);
            $this->applyChannelFilter($channelBuilder, [$channelId]);

            $label = trim((string) $channel->getName());
            if ($label === '') {
                $label = $channelId;
            }

            $channelChoices[$this->formatFacetLabel($label, $this->normalizeCountValue($channelBuilder->count()->getQuery()->execute()))] = $channelId;
        }

        return [
            'page_type_counts' => $pageTypeCounts,
            'status_counts' => $statusCounts,
            'channel_choices' => $channelChoices,
        ];
    }

    /**
     * @return string[]
     */
    private function getAllExistingWebsiteChannelIds(): array
    {
        if (\is_array($this->allExistingWebsiteChannelIds)) {
            return $this->allExistingWebsiteChannelIds;
        }

        $channelIds = [];
        $channels = $this->documentManager->getRepository(Channel::class)->findBy(['type.$id' => 'website']);
        foreach ($channels as $channel) {
            $channelId = $this->resolveChannelId($channel);
            if (null === $channelId) {
                continue;
            }

            $channelIds[] = $channelId;
        }

        $this->allExistingWebsiteChannelIds = array_values(array_unique($channelIds));

        return $this->allExistingWebsiteChannelIds;
    }

    private function isWebsiteChannelId(string $channelId): bool
    {
        return \in_array($channelId, $this->getAllExistingWebsiteChannelIds(), true);
    }

    private function formatFacetLabel(string $label, int $count): string
    {
        return \sprintf('%s %d', $label, $count);
    }

    private function resolveChannelId(mixed $channel): ?string
    {
        if (!\is_object($channel) || !method_exists($channel, 'getId')) {
            return null;
        }

        try {
            $channelId = $channel->getId();
        } catch (\TypeError) {
            return null;
        }

        if (!\is_string($channelId) || $channelId === '') {
            return null;
        }

        return $channelId;
    }

    private function normalizeCountValue(mixed $value): int
    {
        if (\is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return 0;
    }
}
