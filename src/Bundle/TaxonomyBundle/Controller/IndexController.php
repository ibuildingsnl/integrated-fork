<?php

namespace Integrated\Bundle\TaxonomyBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOptions;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Common\Content\Form\ContentFormType;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\Permissions;
use Integrated\Common\Services\Flusher;
use Knp\Component\Pager\Event\Subscriber\Paginate\Callback\CallbackPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class IndexController extends AbstractController
{
    public function __construct(
        private readonly TaxonomyOverview $indexer,
        private readonly ResolverInterface $typeResolver,
        private readonly TaxonomyRepositoryInterface $taxonomies,
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

        if ($form->isSubmitted()) {
            if (!$form->isValid() || !$content instanceof Taxonomy) {
                return $this->redirectToRoute('integrated_content_content_index');
            }

            $this->taxonomies->add($content);

            $this->flusher->flush();

            $this->addFlash('success', 'Taxonomy item created');

            return $this->redirectToRoute('integrated_taxonomy_index', ['type' => $contentType->getId()]);
        }

        $filter = $request->get('filter', 'root');
        $page = $request->query->getInt('page', 1);

        return $this->render('@IntegratedTaxonomy/index/index.html.twig', [
            'form' => $form->createView(),
            'filter_options' => $this->indexer->childrenOf($contentType->getId(), 'root'),
            'filter' => $filter,
            'content_type' => $contentType,
            'index' => $this->paginator->paginate(
                new CallbackPagination(
                    fn () => $this->taxonomies->count($contentType->getId()),
                    fn ($p, $s) => $this->indexer->overviewFor(
                        $contentType->getId(),
                        new TaxonomyOptions($filter, ($p / $s) + 1, $s),
                    ),
                ),
                $page,
                50,
            ),
        ]);
    }
}
