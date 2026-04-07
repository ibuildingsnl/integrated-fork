<?php

namespace Integrated\Bundle\TaxonomyBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOptions;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Common\Content\Form\ContentFormType;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;
use Integrated\Common\Security\Permissions;
use Integrated\Common\Services\Flusher;
use Knp\Component\Pager\Event\Subscriber\Paginate\Callback\CallbackPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class IndexController extends AbstractController
{
    private const PAGE_LIMIT = 50;

    public function __construct(
        private readonly TaxonomyOverview $indexer,
        private readonly ResolverInterface $typeResolver,
        private readonly TaxonomyRepositoryInterface $taxonomies,
        private readonly Flusher $flusher,
        private readonly PaginatorInterface $paginator,
        private readonly MetadataFactoryInterface $metadataFactory,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function index(Request $request, string $type): Response
    {
        $contentType = $this->typeResolver->getType($type);
        $content = $contentType->create();
        $current = $request->query->get('current');
        $currentId = \is_scalar($current) ? trim((string) $current) : '';

        if ('' !== $currentId) {
            $current = $this->taxonomies->byId($currentId);
            if ($current instanceof Taxonomy && $current->getContentType() === $contentType->getId()) {
                if (!$this->isGranted(Permissions::EDIT, $current)) {
                    throw new AccessDeniedException();
                }

                $content = $current;
            }
        }

        if (!$this->isGranted(Permissions::CREATE, $content)) {
            throw new AccessDeniedException();
        }

        if ($request->hasSession() && !$request->query->getBoolean('remember')) {
            $request->getSession()->set('content_redirect_route', [
                'route' => (string) $request->attributes->get('_route'),
                'params' => $request->attributes->get('_route_params', []) + $request->query->all(),
            ]);
        }

        $form = $this->createForm(ContentFormType::class, $content, [
            'method' => 'POST',
            'attr' => [
                'class' => 'content-form',
                'data-content-type' => $contentType->getId(),
            ],
            'content_type' => $contentType,
        ]);

        $isPersisted = '' !== trim((string) $content->getId());
        $form->add('actions', ActionsType::class, ['buttons' => [$isPersisted ? 'save' : 'create']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $content instanceof Taxonomy) {
            $wasPersisted = '' !== trim((string) $content->getId());

            if ($this->dispatcher->hasListeners(Events::POST_VALIDATE)) {
                $this->dispatcher->dispatch(new ValidationEvent(
                    $contentType,
                    $this->getRequiredMetadata($contentType->getClass()),
                    $content,
                ), Events::POST_VALIDATE);
            }

            $this->taxonomies->add($content);

            $this->flusher->flush();

            $this->addFlash('success', $wasPersisted ? 'Taxonomy item saved' : 'Taxonomy item created');

            $params = ['type' => $contentType->getId()] + $request->query->all();
            if ($wasPersisted) {
                $params['current'] = (string) $content->getId();
            } else {
                unset($params['current']);
            }

            return $this->redirectToRoute('integrated_taxonomy_index', $params);
        }

        $filter = $this->resolveFilter($request);
        $page = $request->query->getInt('page', 1);
        $limit = self::PAGE_LIMIT;
        $totalItems = $this->indexer->countFor(
            $contentType->getId(),
            $filter,
        );

        return $this->render('@IntegratedTaxonomy/index/index.html.twig', [
            'form' => $form,
            'filter_options' => $this->indexer->childrenOf($contentType->getId(), 'root'),
            'filter' => $filter,
            'content' => $content,
            'content_type' => $contentType,
            'index' => $this->paginator->paginate(
                new CallbackPagination(
                    fn () => $totalItems,
                    fn ($offset, $limit) => $this->indexer->overviewFor(
                        $contentType->getId(),
                        new TaxonomyOptions($filter, $offset, $limit, false),
                    ),
                ),
                $page,
                $limit,
            ),
            'usage_count_url' => $this->generateUrl('integrated_taxonomy_usage_counts', ['type' => $contentType->getId()]),
            'current_page' => $page,
            'page_limit' => $limit,
        ]);
    }

    public function usageCounts(Request $request, string $type): Response
    {
        $contentType = $this->typeResolver->getType($type);
        $content = $contentType->create();

        if (!$this->isGranted(Permissions::CREATE, $content)) {
            throw new AccessDeniedException();
        }

        $ids = $request->query->all('ids');
        $taxonomyIds = [];

        foreach ($ids as $id) {
            if (!\is_scalar($id)) {
                continue;
            }

            $value = trim((string) $id);
            if ('' === $value) {
                continue;
            }

            $taxonomyIds[$value] = $value;
        }

        return new JsonResponse([
            'counts' => $this->taxonomies->countUsagesFor($taxonomyIds),
        ]);
    }

    private function resolveFilter(Request $request): string
    {
        $filter = $request->query->all()['filter'] ?? 'root';
        if (!\is_scalar($filter)) {
            return 'root';
        }

        $value = trim((string) $filter);

        return '' !== $value ? $value : 'root';
    }

    private function getRequiredMetadata(string $class): MetadataInterface
    {
        $metadata = $this->metadataFactory->getMetadata($class);
        if (!$metadata instanceof MetadataInterface) {
            throw new \LogicException(\sprintf('No form metadata found for "%s".', $class));
        }

        return $metadata;
    }
}
