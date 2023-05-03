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
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelectionRepository;
use Integrated\Bundle\ContentBundle\Form\Type\SearchSelectionType;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\FormTypeBundle\Form\Type\SaveCancelType;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SearchSelectionController extends AbstractController
{
    private RequestStack $requestStack;
    private DocumentManager $documentManager;
    private PaginatorInterface $paginator;
    private SearchContentReferenced $searchContentReferenced;

    public function __construct(
        RequestStack $requestStack,
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        SearchContentReferenced $searchContentReferenced
    ) {
        $this->requestStack = $requestStack;
        $this->documentManager = $documentManager;
        $this->paginator = $paginator;
        $this->searchContentReferenced = $searchContentReferenced;
    }

    public function index(Request $request): Response
    {
        $paginator = $this->paginator->paginate($this->getQueryBuilder(), $request->query->get('page', 1), 15);

        return $this->render('@IntegratedContent/search_selection/index.html.twig', [
            'searchSelections' => $paginator,
        ]);
    }

    public function new(Request $request): Response
    {
        $searchSelection = new SearchSelection();

        $searchSelection->setFilters($request->query->all());
        $searchSelection->setUserId($this->getUser()->getId());

        $form = $this->createCreateForm($searchSelection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->persist($searchSelection);
            $this->documentManager->flush();

            $this->addFlash('success', 'Item created');

            return $this->redirectToRoute('integrated_content_search_selection_index');
        }

        return $this->render('@IntegratedContent/search_selection/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Request $request, SearchSelection $searchSelection): Response
    {
        // TODO: security check

        if ($searchSelection->isLocked()) {
            throw new AccessDeniedException();
        }

        $form = $this->createEditForm($searchSelection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        // TODO: security check

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

            return $this->redirectToRoute('integrated_content_search_selection_index');
        }

        return $this->render('@IntegratedContent/search_selection/delete.html.twig', [
            'searchSelection' => $searchSelection,
            'form' => $form,
            'referenced' => $referenced,
        ]);
    }

    public function menu(): Response
    {
        /** @var Request $request */
        $request = $this->requestStack->getMainRequest();

        /** @var SearchSelectionRepository $repo */
        $repo = $this->documentManager->getRepository(SearchSelection::class);

        $user = $this->getUser();

        return $this->render('@IntegratedContent/search_selection/menu.html.twig', [
            'filters' => $request ? $request->query->all() : [],
            'searchSelections' => $user ? $repo->findPublicByUserId($user->getId()) : [],
        ]);
    }

    private function createCreateForm(SearchSelection $searchSelection): FormInterface
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();

        $form = $this->createForm(SearchSelectionType::class, $searchSelection, [
            'action' => $this->generateUrl('integrated_content_search_selection_new', $request ? $request->query->all() : []),
        ]);

        $form->add('actions', SaveCancelType::class, [
            'cancel_route' => 'integrated_content_search_selection_index',
            'label' => 'Create',
            'button_class' => '',
        ]);

        return $form;
    }

    private function createEditForm(SearchSelection $searchSelection): FormInterface
    {
        $form = $this->createForm(SearchSelectionType::class, $searchSelection, [
            'action' => $this->generateUrl('integrated_content_search_selection_edit', ['id' => $searchSelection->getId()]),
        ]);

        $form->add('actions', SaveCancelType::class, ['cancel_route' => 'integrated_content_search_selection_index']);

        return $form;
    }

    private function createDeleteForm(string $id, bool $notDelete = false): FormInterface
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('integrated_content_search_selection_delete', ['id' => $id]));

        if ($notDelete) {
            $form->add('reload', SubmitType::class, ['label' => 'Reload', 'attr' => ['class' => 'btn-default']]);
        } else {
            $form->add('submit', SubmitType::class, ['label' => 'Delete', 'attr' => ['class' => 'btn-danger']]);
        }

        return $form->getForm();
    }

    private function getQueryBuilder(): Builder
    {
        $builder = $this->documentManager->createQueryBuilder(SearchSelection::class);

        if (false === $this->isGranted('ROLE_ADMIN')) {
            $builder->field('userId')->equals($this->getUser()->getId());
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
}
