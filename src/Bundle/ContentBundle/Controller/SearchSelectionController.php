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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type\SearchSelectionType;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\IntegratedBundle\Controller\PaginationQueryTrait;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SearchSelectionController extends AbstractController
{
    use PaginationQueryTrait;
    use SearchSelectionSortingSettingsTrait;

    private RequestStack $requestStack;
    private DocumentManager $documentManager;
    private PaginatorInterface $paginator;
    private SearchContentReferenced $searchContentReferenced;

    public function __construct(
        RequestStack $requestStack,
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        SearchContentReferenced $searchContentReferenced,
    ) {
        $this->requestStack = $requestStack;
        $this->documentManager = $documentManager;
        $this->paginator = $paginator;
        $this->searchContentReferenced = $searchContentReferenced;
    }

    public function index(Request $request): Response
    {
        $paginator = $this->paginator->paginate($this->getQueryBuilder(), $this->getPositiveIntQueryParameter($request, 'page', 1), 25);

        return $this->render('@IntegratedContent/search_selection/index.html.twig', [
            'searchSelections' => $paginator,
        ]);
    }

    public function new(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException();
        }

        $searchSelection = new SearchSelection();

        $searchSelection->setFilters($request->query->all());
        $searchSelection->setUserId((int) $user->getId());

        $form = $this->createCreateForm($searchSelection);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_content_search_selection_index');
            }
            if ($form->isValid()) {
                $searchSelection->setFilters($this->applySearchSelectionSortingSettings($form, $searchSelection->getFilters()));
                $this->documentManager->persist($searchSelection);
                $this->documentManager->flush();

                $this->addFlash('success', 'Item created');

                return $this->redirectToRoute('integrated_content_search_selection_index');
            }
        }

        return $this->render('@IntegratedContent/search_selection/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Request $request, SearchSelection $searchSelection): Response
    {
        $this->assertSearchSelectionAccess($searchSelection);

        if ($searchSelection->isLocked()) {
            throw new AccessDeniedException();
        }

        $form = $this->createEditForm($searchSelection, $request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->query->get('searchSelection') === $searchSelection->getId()) {
                $searchSelection->setFilters($request->query->all());
            }
            $searchSelection->setFilters($this->applySearchSelectionSortingSettings($form, $searchSelection->getFilters()));

            $this->documentManager->flush();

            $this->addFlash('success', 'Item updated');

            return $this->redirectToRoute('integrated_content_search_selection_index');
        }

        return $this->render('@IntegratedContent/search_selection/edit.html.twig', [
            'form' => $form,
        ]);
    }

    public function delete(Request $request, SearchSelection $searchSelection): Response
    {
        $this->assertSearchSelectionAccess($searchSelection);

        if ($searchSelection->isLocked()) {
            throw new AccessDeniedException();
        }

        $referenced = $this->searchContentReferenced->getReferenced($searchSelection);

        $form = $this->createDeleteForm($searchSelection->getId(), \count($referenced) > 0);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->remove($searchSelection);
            $this->documentManager->flush();

            $this->addFlash('success', 'Item deleted');

            return $this->redirectToRoute('integrated_content_content_index');
        }

        return $this->render('@IntegratedContent/search_selection/delete.html.twig', [
            'searchSelection' => $searchSelection,
            'form' => $form,
            'referenced' => $referenced,
        ]);
    }

    /**
     * Creates a form to create a SearchSelection document.
     *
     * @return FormInterface
     */
    protected function createCreateForm(SearchSelection $searchSelection)
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();

        $form = $this->createForm(
            SearchSelectionType::class,
            $searchSelection,
            [
                'action' => $this->generateUrl(
                    'integrated_content_search_selection_new',
                    $request ? $request->query->all() : []
                ),
                'method' => 'POST',
            ]
        );

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    /**
     * Creates a form to edit a SearchSelection document.
     *
     * @return FormInterface
     */
    protected function createEditForm(SearchSelection $searchSelection, Request $request)
    {
        $form = $this->createForm(
            SearchSelectionType::class,
            $searchSelection,
            [
                'action' => $this->generateUrl(
                    'integrated_content_search_selection_edit',
                    ['id' => $searchSelection->getId()] + $request->query->all()
                ),
                'method' => 'PUT',
            ]
        );

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    /**
     * Creates a form to delete a SearchSelection document by id.
     *
     * @param bool|false $notDelete
     *
     * @return FormInterface
     */
    protected function createDeleteForm($id, bool $notDelete = false)
    {
        $form = $this->createFormBuilder()
                     ->setAction($this->generateUrl('integrated_content_search_selection_delete', ['id' => $id]))
                     ->setMethod(Request::METHOD_DELETE);

        if ($notDelete) {
            $form->add('actions', ActionsType::class, ['buttons' => ['reload', 'cancel']]);
        } else {
            $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);
        }

        return $form->getForm();
    }

    private function getQueryBuilder(): Builder
    {
        $builder = $this->documentManager->createQueryBuilder(SearchSelection::class);

        if (false === $this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user instanceof UserInterface) {
                throw new AccessDeniedException();
            }

            $builder->field('userId')->equals((int) $user->getId());
        }

        return $builder;
    }

    protected function getUser(): ?UserInterface
    {
        $user = parent::getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

    private function assertSearchSelectionAccess(SearchSelection $searchSelection): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException();
        }

        if ((int) $searchSelection->getUserId() !== (int) $user->getId()) {
            throw new AccessDeniedException();
        }
    }
}
