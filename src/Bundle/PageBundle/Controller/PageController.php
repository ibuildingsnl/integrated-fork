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

class PageController extends AbstractController
{
    private DocumentManager $documentManager;
    private PaginatorInterface $paginator;
    private PageCopyService $pageCopyService;
    private RouteCache $routeCache;

    public function __construct(
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        PageCopyService $pageCopyService,
        RouteCache $routeCache,
    ) {
        $this->documentManager = $documentManager;
        $this->paginator = $paginator;
        $this->pageCopyService = $pageCopyService;
        $this->routeCache = $routeCache;
    }

    public function index(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $filterForm = $this->createForm(
            PageFilterType::class,
            $request->getSession()->get('page_filterform_data', []),
            ['method' => 'GET']
        );
        $filterForm->handleRequest($request);

        switch ($filterForm->get('pagetype')->getData()) {
            case 'page':
                $class = Page::class;
                break;
            case 'contenttype':
                $class = ContentTypePage::class;
                break;
            default:
                $class = AbstractPage::class;
        }

        $builder = $this->documentManager->createQueryBuilder($class);

        $this->displayPathErrors($builder);

        if ($query = $filterForm->get('q')->getData()) {
            $builder->addOr($builder->expr()->field('title')->equals(new Regex('/'.$query.'/i')));
            $builder->addOr($builder->expr()->field('path')->equals(new Regex('/'.$query.'/i')));
        }

        if ($channel = $filterForm->get('channel')->getData()) {
            $builder->field('channel.$id')->equals($channel);
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
        ]);

        return $response;
    }

    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $page = new Page();

        $form = $this->createCreateForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->persist($page);
            $this->documentManager->flush();

            $this->routeCache->clear();

            $this->addFlash('success', \sprintf('Page "%s" has been created', $page->getTitle()));

            $this->setLastEditPage($request->getSession(), $page);

            return $this->redirectToRoute('integrated_page_page_index');
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

        if ($formData = $request->request->get('page_copy', null)) {
            $targetChannel = $formData['targetChannel'] ?? null;
            $sourceChannel = $formData['sourceChannel'] ?? null;
        } else {
            $targetChannel = null;
            $sourceChannel = null;
        }

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
}
