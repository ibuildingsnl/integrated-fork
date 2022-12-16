<?php

namespace Integrated\Bundle\TaxonomyBundle\Controller;

use Integrated\Bundle\ContentBundle\Services\ContentCreator;
use Integrated\Bundle\ContentBundle\Services\Flusher;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class IndexController extends AbstractController
{
    public function __construct(
        private readonly TaxonomyIndexerInterface $indexer,
        private readonly ContentCreator $creator,
        private readonly Flusher $flusher,
    ) {}

    public function taxonomyIndexPage(Request $request): Response
    {
        $request->attributes->set('type', 'taxonomy');
        $form = $this->creator->new($request, 'integrated_taxonomy_index');

        if (null === $form) {
            return $this->redirectToRoute('integrated_content_content_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->flusher->flush();
            $this->addFlash('success', 'Taxonomy created');
        }

        return $this->render('@IntegratedTaxonomy/page/page.html.twig', [
            'form' => $form->createView(),
            'index' => $this->indexer->buildTaxonomyIndex(),
        ]);
    }
}
