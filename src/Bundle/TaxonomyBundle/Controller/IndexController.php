<?php

namespace Integrated\Bundle\TaxonomyBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Services\Flusher;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepository;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexerInterface;
use Integrated\Common\Content\Form\ContentFormType;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\Permissions;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class IndexController extends AbstractController
{
    public function __construct(
        private readonly TaxonomyIndexerInterface $indexer,
        private readonly ResolverInterface $typeResolver,
        private readonly TaxonomyRepository $taxonomies,
        private readonly Flusher $flusher,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    public function index(Request $request): Response
    {
        $contentType = $this->typeResolver->getType($request->get('type', 'taxonomy'));

        $content = $contentType->create();

        if (!$this->isGranted(Permissions::CREATE, $content)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ContentFormType::class, $content, [
            'method' => 'POST',
            'attr' => [
                'class' => 'content-form',
                'data-content-type' => $contentType->getId(),
            ],
            'content_type' => $contentType,
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->render('@IntegratedTaxonomy/index/index.html.twig', [
                'form' => $form->createView(),
                'index' => $this->paginator->paginate(
                    $this->indexer->buildTaxonomyIndex(),
                    $request->query->getInt('page', 1),
                    25,
                ),
            ]);
        }

        if (!$form->isValid() || !$content instanceof Taxonomy) {
            return $this->redirectToRoute('integrated_content_content_index');
        }

        $this->taxonomies->add($content);

        $this->flusher->flush();

        $this->addFlash('success', 'Taxonomy item created');
        return $this->redirectToRoute('integrated_taxonomy_index');
    }
}
