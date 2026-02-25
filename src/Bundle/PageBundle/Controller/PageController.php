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
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Bundle\PageBundle\Form\Type\PageCopyType;
use Integrated\Bundle\PageBundle\Form\Type\PageFilterType;
use Integrated\Bundle\PageBundle\Form\Type\PageType;
use Integrated\Bundle\PageBundle\Services\PageCopyService;
use Integrated\Bundle\PageBundle\Services\RouteCache;
use Knp\Component\Pager\PaginatorInterface;
use MongoDB\BSON\Regex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\UriSigner;

class PageController extends AbstractController
{
    private const PREVIEW_LINK_TTL_SECONDS = 86400;
    private const PREVIEW_EXPIRES_PARAM = 'preview_expires';

    private DocumentManager $documentManager;
    private PaginatorInterface $paginator;
    private PageCopyService $pageCopyService;
    private RouteCache $routeCache;
    private UriSigner $uriSigner;

    public function __construct(
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        PageCopyService $pageCopyService,
        RouteCache $routeCache,
        UriSigner $uriSigner,
    ) {
        $this->documentManager = $documentManager;
        $this->paginator = $paginator;
        $this->pageCopyService = $pageCopyService;
        $this->routeCache = $routeCache;
        $this->uriSigner = $uriSigner;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $requestFilterData = $request->query->all('page_filter');
        if (\is_array($requestFilterData)) {
            $request->query->set('page_filter', $this->normalizePageFilterData($requestFilterData));
        }

        $filterForm = $this->createForm(
            PageFilterType::class,
            $this->normalizePageFilterData($request->getSession()->get('page_filterform_data', [])),
            [
                'method' => 'GET',
                'action' => $this->generateUrl('integrated_page_page_index'),
            ]
        );
        $filterForm->handleRequest($request);

        $pageTypes = $filterForm->get('pagetype')->getData();
        if (!\is_array($pageTypes)) {
            $pageTypes = \is_string($pageTypes) && $pageTypes !== '' ? [$pageTypes] : [];
        }
        $pageTypes = \array_values(\array_unique(\array_filter(
            $pageTypes,
            static fn ($value): bool => \in_array($value, ['page', 'contenttype'], true)
        )));

        if ($pageTypes === ['page']) {
            $class = Page::class;
        } elseif ($pageTypes === ['contenttype']) {
            $class = ContentTypePage::class;
        } else {
            $class = AbstractPage::class;
        }

        $builder = $this->documentManager->createQueryBuilder($class);

        $this->displayPathErrors($builder);

        if ($query = $filterForm->get('q')->getData()) {
            $escapedQuery = preg_quote((string) $query, '/');
            $builder->addOr($builder->expr()->field('title')->equals(new Regex($escapedQuery, 'i')));
            $builder->addOr($builder->expr()->field('path')->equals(new Regex($escapedQuery, 'i')));
        }

        $channels = $filterForm->get('channel')->getData();
        if (\is_array($channels)) {
            $channels = \array_values(\array_filter($channels, static fn ($channel): bool => \is_string($channel) && $channel !== ''));
            if ($channels !== []) {
                $builder->field('channel.$id')->in($channels);
            }
        } elseif (\is_string($channels) && $channels !== '') {
            $builder->field('channel.$id')->equals($channels);
        }

        $statuses = $filterForm->get('status')->getData();
        if (!\is_array($statuses)) {
            $statuses = \is_string($statuses) && $statuses !== '' ? [$statuses] : [];
        }
        $statuses = \array_values(\array_unique(\array_filter(
            $statuses,
            static fn ($value): bool => \in_array($value, ['published', 'draft'], true)
        )));

        $filterPublished = \in_array('published', $statuses, true);
        $filterDraft = \in_array('draft', $statuses, true);

        if ($filterPublished && !$filterDraft) {
            if ($class === Page::class) {
                $builder->field('disabled')->equals(false);
            } elseif ($class === AbstractPage::class) {
                // ContentTypePage has no "disabled" field and is always published.
                $builder->field('disabled')->notEqual(true);
            }
        } elseif ($filterDraft && !$filterPublished) {
            if ($class === Page::class || $class === AbstractPage::class || $class === ContentTypePage::class) {
                $builder->field('disabled')->equals(true);
            }
        }

        $builder->sort('path', 1);
        $builder->sort('channel.$id', 1);

        if ($filterForm->isSubmitted()) {
            $request->getSession()->set('page_filterform_data', $filterForm->getData());
        }

        $pagination = $this->paginator->paginate(
            $builder,
            $request->query->get('page', 1),
            25
        );

        $response = $this->render('@IntegratedPage/page/index.html.twig', [
            'pages' => $pagination,
            'filterForm' => $filterForm,
            'lastPage' => $this->getLastEditPage($request->getSession()),
            'previewLinks' => $this->buildPreviewLinks($pagination, $request),
        ]);

        return $response;
    }

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

    private function normalizeMultiSelectFilterValue(mixed $value): array
    {
        if (\is_array($value)) {
            $values = $value;
        } elseif (\is_scalar($value) && (string) $value !== '') {
            $values = [(string) $value];
        } else {
            $values = [];
        }

        $values = \array_values(\array_filter($values, static fn ($item): bool => \is_scalar($item) && (string) $item !== ''));

        return \array_map(static fn ($item): string => (string) $item, $values);
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
        $targetChannel = $formData['targetChannel'] ?? null;
        $sourceChannel = $formData['sourceChannel'] ?? null;

        $form = $this->createForm(
            PageCopyType::class,
            null,
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
                $this->pageCopyService->copyPages($form->getData());

                $this->addFlash('success', 'Pages copied');

                return $this->redirectToRoute('integrated_page_page_index');
            }
        }

        return $this->render('@IntegratedPage/page/copy.html.twig', [
            'form' => $form,
        ]);
    }

    private function createCreateForm(Page $page): FormInterface
    {
        $form = $this->createForm(PageType::class, $page, [
            'action' => $this->generateUrl('integrated_page_page_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
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
        foreach ($builder->getQuery()->execute() as $item) {
            if (!$item instanceof ContentTypePage) {
                continue;
            }

            $settings = $item->getControllerService().$item->getLayout();
            $key = $item->getChannel()->getId().'-'.$item->getPath();
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

    private function buildAbsolutePageUrl(Page $page, Request $request, array $query = []): string
    {
        $host = (string) ($page->getDomain() ?: $request->getHost());
        $scheme = $request->getScheme();
        $path = (string) $page->getPath();
        $queryString = http_build_query($query, '', '&', \PHP_QUERY_RFC3986);

        return $scheme.'://'.$host.$path.($queryString !== '' ? '?'.$queryString : '');
    }
}
