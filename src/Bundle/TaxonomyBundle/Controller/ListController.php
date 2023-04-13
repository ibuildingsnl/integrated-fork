<?php

namespace Integrated\Bundle\TaxonomyBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Common\Content\Form\ContentFormType;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\Permissions;
use Integrated\Common\Services\Flusher;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Solarium\Core\Client\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

// @todo remove & solve in service layer
final class ListController extends AbstractController
{
    public function __construct(
        private readonly QueryFactoryInterface       $queryFactory,
        private readonly Client                      $solr,
        private readonly ResolverInterface           $typeResolver,
        private readonly TaxonomyRepositoryInterface $taxonomies,
        private readonly Flusher                     $flusher,
        private readonly PaginatorInterface          $paginator,
    ) {
    }

    public function index(Request $request): Response
    {
        // @todo abstract away duplication?
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

            return $this->redirectToRoute('integrated_taxonomy_list', ['type' => $contentType->getId()]);
        }

        $options = ['contenttypes' => [$contentType]];
        $options = [];

        $query = $this->queryFactory->createQuery(IntegratedContent::class, $options);

        // @todo paginate & map
        $paginator = $this->paginator->paginate(
            [$this->solr, $query->getQuery()],
            $request->query->get('page', 1),
            $request->query->get('limit', 15),
            [PaginatorInterface::SORT_FIELD_PARAMETER_NAME => null]
        );
        $items = $paginator->getItems();
        $items = $items instanceof \Traversable ? iterator_to_array($items) : $items;
        dd($items);
        return $this->render('@IntegratedTaxonomy/index/index.html.twig', [
            'form' => $form->createView(),
            'content_type' => $contentType,
            'index' => array_map(
                fn(Taxonomy $t) => 1,
                $items,
            ),
        ]);
    }
}
