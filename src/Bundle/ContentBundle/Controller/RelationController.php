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
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Form\Type\RelationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RelationController extends AbstractController
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $qb = $this->documentManager->createQueryBuilder(Relation::class)
            ->sort('name');

        if ($contentType = $request->get('contentType')) {
            $qb->field('sources.$id')->in([(string) $contentType]);
        }

        $documents = $qb->getQuery()->execute();

        return $this->render(sprintf('@IntegratedContent/relation/index.%s.twig', $request->getRequestFormat()), ['documents' => $documents]);
    }

    public function show(Relation $relation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createDeleteForm($relation);

        return $this->render('@IntegratedContent/relation/show.html.twig', [
            'form' => $form,
            'relation' => $relation,
        ]);
    }

    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $relation = new Relation();

        $form = $this->createNewForm($relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->persist($relation);
            $this->documentManager->flush();

            $this->addFlash('success', 'Item created');

            return $this->redirectToRoute('integrated_content_relation_index');
        }

        return $this->render('@IntegratedContent/relation/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Request $request, Relation $relation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createEditForm($relation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->flush();

            $this->addFlash('success', 'Item updated');

            return $this->redirectToRoute('integrated_content_relation_index');
        }

        return $this->render('@IntegratedContent/relation/edit.html.twig', [
            'form' => $form,
        ]);
    }

    public function delete(Request $request, Relation $relation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createDeleteForm($relation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->remove($relation);
            $this->documentManager->flush();

            $this->addFlash('success', 'Item deleted');
        }

        return $this->redirectToRoute('integrated_content_relation_index');
    }

    private function createNewForm(Relation $relation): FormInterface
    {
        $form = $this->createForm(RelationType::class, $relation, [
            'action' => $this->generateUrl('integrated_content_relation_new'),
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    private function createEditForm(Relation $relation): FormInterface
    {
        $form = $this->createForm(RelationType::class, $relation, [
            'action' => $this->generateUrl('integrated_content_relation_edit', ['id' => $relation->getId()]),
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    private function createDeleteForm(Relation $relation): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('integrated_content_relation_delete', ['id' => $relation->getId()]))
            ->add('submit', SubmitType::class, [
                'label' => 'Delete',
                'attr' => [
                    'class' => 'btn-danger',
                    'onclick' => 'return confirm(\'Are you sure you want to delete this relation?\');',
                ],
            ])
            ->getForm()
        ;
    }
}
