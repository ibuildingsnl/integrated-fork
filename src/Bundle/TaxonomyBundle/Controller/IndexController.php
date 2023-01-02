<?php

namespace Integrated\Bundle\TaxonomyBundle\Controller;

use Integrated\Bundle\ContentBundle\Services\ContentCreator;
use Integrated\Bundle\ContentBundle\Services\Flusher;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class IndexController extends AbstractController
{
    public function __construct(
        private readonly TaxonomyIndexerInterface $indexer,
        private readonly ContentCreator $creator,
        private readonly Flusher $flusher,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    public function taxonomyIndexPage(Request $request): Response
    {
        $request->attributes->set('type', 'taxonomy');
        $form = $this->creator->new($request, 'integrated_taxonomy_index');

        if (null === $form) {
            return $this->redirectToRoute('integrated_content_content_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->flusher->flush();
            $this->addFlash('success', 'Taxonomy item created');

            return $this->redirectToRoute('integrated_taxonomy_index');
        }

        return $this->render('@IntegratedTaxonomy/page/taxonomy_index.html.twig', [
            'form' => $form->createView(),
            'index' => $this->paginator->paginate(
                $this->indexer->buildTaxonomyIndex(),
                $request->query->getInt('page', 1),
                25,
            ),
        ]);
    }
}
