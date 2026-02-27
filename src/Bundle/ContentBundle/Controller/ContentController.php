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
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelectionRepository;
use Integrated\Bundle\ContentBundle\Event\ContentDeletedEvent;
use Integrated\Bundle\ContentBundle\Event\ContentDistributedEvent;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\ContentBundle\Form\Type\SearchSelectionType;
use Integrated\Bundle\ContentBundle\Provider\MediaProvider;
use Integrated\Bundle\ContentBundle\Services\CalendarOptions;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\Form\ContentFormType;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Locks;
use Integrated\Common\Locks\Filter;
use Integrated\Common\Locks\LockInterface;
use Integrated\Common\Locks\Provider\DBAL\Manager;
use Integrated\Common\Locks\Resource;
use Integrated\Common\Locks\ResourceInterface;
use Integrated\Common\Queue\Provider\DBAL\QueueProvider;
use Integrated\Common\Security\Permissions;
use Integrated\Common\Solr\Configurable;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ContentController extends AbstractController
{
    private const NAVDROPDOWNS_CACHE_NAMESPACE = 'integrated_content_fragments_navdropdowns';
    private const CONTENT_LOCK_TIMEOUT_SECONDS = 15;

    /**
     * @var string
     */
    protected $relationClass = 'Integrated\\Bundle\\ContentBundle\\Document\\Relation\\Relation';

    public function __construct(
        private readonly ResolverInterface $resolver,
        private readonly ContentTypeManager $contentTypeManager,
        private readonly QueueSubscriber $queueSubscriber,
        private readonly LockFactory $lockFactory,
        private readonly IndexerInterface $indexer,
        private readonly SearchContentReferenced $contentReferenced,
        private readonly Manager $lockManager,
        private readonly UserManagerInterface $userManager,
        private readonly ImageExtension $imageExtension,
        private readonly MediaProvider $mediaProvider,
        private readonly TaxonomyOverview $taxonomyIndexer,
        private readonly QueryFactoryInterface $queryFactory,
        private readonly MetadataFactoryInterface $metadataFactory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly DocumentManager $documentManager,
        private readonly CalendarOptions $calendarOptions,
        private readonly QueueProvider $queueProvider,
    ) {
    }

    public function index(Request $request, string $searchSelection = 'all'): array|Response
    {
        // remember search state
        $session = $request->getSession();

        if ($request->query->get('remember')) {
            if ($session->has('content_redirect_route')) {
                $route = $session->get('content_redirect_route', []);

                return $this->redirectToRoute($route['route'], $route['params'] ?? []);
            } elseif ($session->has('content_index_view')) {
                $request->query->add(unserialize($session->get('content_index_view')));
                $request->query->remove('remember');
            }
        } elseif ($request->getRequestFormat() !== 'json') {
            $session->set('content_index_view', serialize($request->query->all()));
            $session->remove('content_redirect_route');
        }

        $options = $request->query->all();
        unset($options['searchSelection'], $options['page']);

        /** @var SearchSelection|null $selection */
        $selection = null;
        if ($searchSelection && $searchSelection !== 'all') {
            $selection = $this->getDoctrineODM()
                              ->getRepository(SearchSelection::class)
                              ->find($searchSelection);
            if ($selection) {
                $selectionFilters = $selection->getFilters();
                $options = array_merge($selectionFilters, $options);
            }
        }

        $newSelection = false;
        if (!$selection) {
            $newSelection = true;
            $selection = new SearchSelection();
        }
        $editableSelection = $this->isGranted('ROLE_ADMIN') || (
            !$selection->isPublic()
            && $selection->getUserId() === $this->getUser()->getId()
        );

        $searchSelectionForm = $this->createForm(SearchSelectionType::class, $selection);
        $searchSelectionForm->add('actions', ActionsType::class, [
            'buttons' => $newSelection || !$editableSelection ? ['create'] : ['save', 'create'],
        ]);
        $searchSelectionForm->handleRequest($request);
        if ($searchSelectionForm->isSubmitted() && $searchSelectionForm->isValid()) {
            if ($searchSelectionForm->get('actions')->getData() === 'create') {
                $newSelection = true;
                $this->documentManager->detach($selection);
                $selection = clone $selection;
                $selection->setId(null);
                $selection->setUserId($this->getUser()->getId());
                if (!$editableSelection) {
                    $selection->setPublic(false);
                }
            } elseif (!$editableSelection) {
                throw new AccessDeniedException();
            }
            $selection->setFilters($options);
            $this->documentManager->persist($selection);
            $this->documentManager->flush();

            $this->addFlash('success', 'Selection saved');
            if ($newSelection) {
                return $this->redirectToRoute(
                    'integrated_content_content_selection',
                    ['searchSelection' => $selection->getId()]
                );
            }
        }

        // view settings (calendar etc)

        $view = '';
        if (!empty($options['view']) && $options['view'] != 'list') {
            $request->query->set('page', 1);
            $request->query->set('limit', 1000);
            $options = $this->calendarOptions->prepare($options);
            $view = $options['_view'] ?? '';
            unset($options['_view']);
        }

        // all this relations stuff is only used on the json response
        $relations = [];
        if ($options['relation'] ?? null) {
            $options['contenttypes'] = [];

            if ($relation = $this->getDoctrineODM()->getRepository(Relation::class)->find($options['relation'])) {
                foreach ($relation->getTargets() as $target) {
                    $options['contenttypes'][] = $target->getId();
                    $relations[] = [
                        'href' => $this->generateUrl(
                            'integrated_content_content_new',
                            [
                                'class' => $target->getClass(),
                                'type' => $target->getId(),
                                'relation' => $relation->getId(),
                            ]
                        ),
                        'name' => $target->getName(),
                    ];
                }
            }
        }

        if ($request->isMethod('post') && $request->get('id')) {
            $options['ids'] = $request->get('id');
        }

        $client = $this->getSolarium();
        $client->getPlugin('postbigrequest');

        $query = $this->queryFactory->createQuery(IntegratedContent::class, $options);

        $paginator = $this->getPaginator()->paginate(
            [$client, $query->getQuery()],
            $request->query->get('page', 1),
            $request->query->get('limit', 25),
            [PaginatorInterface::SORT_FIELD_PARAMETER_NAME => null]
        );

        /** @var SearchSelectionRepository $repo */
        $repo = $this->documentManager->getRepository(SearchSelection::class);

        return $this->render(
            '@IntegratedContent/content/index'.$view.'.'.$request->getRequestFormat().'.twig',
            [
                'params' => $query->getOptions(),
                'pager' => $paginator,
                'facets' => $paginator->getCustomParameter('result')?->getFacetSet()?->getFacets(),
                'locks' => $this->getLocks($paginator),
                'relations' => $relations,
                'selection' => $selection,
                'isSelectionEditable' => $editableSelection,
                'searchSelections' => $this->getUser() ? $repo->findForUser($this->getUser()) : [],
                'searchSelectionForm' => $searchSelectionForm,
                'contentTypes' => $this->contentTypeManager->getAll(),
                'route' => $request->attributes->get('_route'),
                'queryParams' => array_merge($request->query->all(), $options),
            ]
        );
    }

    public function show(Request $request, Content $content): Response
    {
        return $this->render('@IntegratedContent/content/show.'.$request->getRequestFormat().'.twig', [
            'document' => $content,
        ]);
    }

    public function new(Request $request): Response
    {
        /** @var ContentTypeInterface $contentType */
        $contentType = $this->contentTypeManager->getType($request->get('type'));

        $content = $contentType->create();

        if (!$this->isGranted(Permissions::CREATE, $content)) {
            throw new AccessDeniedException();
        }

        $form = $this->createNewForm($contentType, $content, $request);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_content_content_index', ['remember' => 1]);
            }

            if ($form->isValid()) {
                if ($this->dispatcher->hasListeners(Events::POST_VALIDATE)) {
                    $this->dispatcher->dispatch(
                        new ValidationEvent(
                            $contentType,
                            $this->metadataFactory->getMetadata($contentType->getClass()),
                            $content,
                        ),
                        Events::POST_VALIDATE
                    );
                }
                // higher priority for content edited in Integrated
                $queue = $this->queueSubscriber->getQueue();
                $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);

                $this->documentManager->persist($content);
                $this->documentManager->flush();

                if ($this->dispatcher->hasListeners(Events::CONTENT_DISTRIBUTED)) {
                    $this->dispatcher->dispatch(
                        new ContentDistributedEvent($content),
                        Events::CONTENT_DISTRIBUTED
                    );
                }

                $lock = $this->lockFactory->createLock(self::class);
                $lock->acquire(true);

                try {
                    if ($this->indexer instanceof Configurable) {
                        $this->indexer->setOption('queue.size', 2);
                    }

                    $this->indexer->execute(); // lets hope that the gods of random is in our favor as there is no way to guarantee that this will do what we want
                } finally {
                    $lock->release();
                }

                if ($request->getRequestFormat() == 'iframe.html') {
                    return $this->render(
                        '@IntegratedContent/content/saved.iframe.html.twig',
                        [
                            'id' => $content->getId(),
                            'title' => method_exists($content, 'getTitle') ? $content->getTitle() : $content->getId(),
                            'relation' => $request->get('relation'),
                        ]
                    );
                }

                // Set flash message
                $this->addFlash(
                    'success',
                    $this->getTranslator()->trans(
                        'The document %name% has been created',
                        ['%name%' => $contentType->getName()]
                    )
                );

                return $this->redirectToRoute(
                    'integrated_content_content_edit',
                    ['id' => $content->getId()]
                );
            }
        }

        return $this->render(\sprintf('@IntegratedContent/content/new.%s.twig', $request->getRequestFormat()), [
            'taxonomyCategories' => $this->getTaxonomyCategories($content),
            'editable' => true,
            'type' => $contentType,
            'form' => $form,
            'showContentHistory' => false,
            'references' => json_encode($this->getReferences($content)),
        ]);
    }

    private function getTaxonomyCategories($content): array
    {
        $contentRelations = [];
        $contentType = $this->contentTypeManager->getType($content->getContentType());
        $relations = $this->documentManager->getRepository($this->relationClass)->findAll();
        foreach ($relations as $relation) {
            if ($relation->hasSource($contentType) && $relation->getType() == 'taxonomy_category') {
                $contentRelations[] = $relation;
            }
        }

        $taxonomyCategories = [];
        foreach ($contentRelations as $contentRelation) {
            foreach ($contentRelation->getTargets() as $target) {
                $taxonomyCategories[$contentRelation->getId()] = $this->taxonomyIndexer->overviewFor($target->getId());
            }
        }

        return $taxonomyCategories;
    }

    /**
     * Update a existing document.
     */
    public function edit(Request $request, string $id): Response
    {
        $content = $this->findContentByIdentifier($id);

        if (!$content) {
            throw $this->createNotFoundException('Content not found.');
        }

        /** @var ContentTypeInterface $contentType */
        $contentType = $this->contentTypeManager->getType($content->getContentType());

        if (!$this->isGranted(Permissions::VIEW, $content)) {
            throw new AccessDeniedException();
        }

        $prefetchRequest = $this->isPrefetchRequest($request);
        $hasRequestedLock = $request->query->has('lock') && '' !== trim((string) $request->query->get('lock'));
        $locking = $prefetchRequest
            ? $this->createPendingLocking()
            : ($hasRequestedLock
                ? $this->getLock($content, self::CONTENT_LOCK_TIMEOUT_SECONDS)
                : $this->getExistingLock($content));
        $locking['locked'] = $locking['pending'] || (bool) $locking['lock'];

        if (true === $content instanceof File) {
            $locking['locked'] = false;
            $locking['pending'] = false;
        } else {
            if ($locking['lock'] && $locking['owner']) {
                $requestedLockId = trim((string) $request->query->get('lock', ''));
                $currentLockId = $locking['lock']->getId();

                if ($locking['new']) {
                    $locking['locked'] = false;

                    if ($request->isMethod('get') && $requestedLockId !== $currentLockId) {
                        $parameters = array_merge($request->query->all(), [
                            'id' => $content->getId(),
                            'lock' => $currentLockId,
                        ]);

                        return $this->redirectToRoute($request->get('_route'), $parameters);
                    }
                } elseif ($requestedLockId === $currentLockId) {
                    // Reusing a lock requires the lock token in the URL for this tab.
                    $locking['locked'] = false;
                }
            }
        }

        $form = $this->createEditForm($contentType, $content, $locking, $request);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // possible actions are cancel, back, reload, reload_changed and save
            $submittedActionData = $form->get('actions')->getData();
            $submittedAction = $this->resolveSubmittedAction(
                $submittedActionData,
                $request,
                ['cancel', 'back', 'reload', 'save', 'reload_changed']
            );

            if ($submittedAction === 'cancel' || $submittedAction === 'back') {
                if (!$locking['locked']) {
                    $locking['release']();
                }

                $url = $form->get('returnUrl')->getData() ?: $this->generateUrl(
                    'integrated_content_content_index',
                    ['remember' => 1]
                );

                return $this->redirect($url);
            }

            if (!$this->isGranted(Permissions::EDIT, $content)) {
                throw new AccessDeniedException();
            }

            if ($submittedAction === 'reload') {
                $parameters = array_merge($request->query->all(), ['id' => $content->getId()]);
                if (!$locking['locked'] && $locking['lock'] && $locking['owner']) {
                    $parameters['lock'] = $locking['lock']->getId();
                } else {
                    unset($parameters['lock']);
                }

                return $this->redirectToRoute($request->get('_route'), $parameters);
            }

            // this is not rest compatible since a button click is required to save
            if ($submittedAction === 'save') {
                $saved = false;

                if (!$locking['locked'] && $form->isValid()) {
                    if ($this->dispatcher->hasListeners(Events::POST_VALIDATE)) {
                        $this->dispatcher->dispatch(
                            new ValidationEvent(
                                $contentType,
                                $this->metadataFactory->getMetadata($contentType->getClass()),
                                $content,
                            ),
                            Events::POST_VALIDATE
                        );
                    }

                    // higher priority for content edited in Integrated
                    $queue = $this->queueSubscriber->getQueue();
                    $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);

                    $this->documentManager->flush();

                    if ($this->dispatcher->hasListeners(Events::CONTENT_DISTRIBUTED)) {
                        $this->dispatcher->dispatch(
                            new ContentDistributedEvent($content),
                            Events::CONTENT_DISTRIBUTED
                        );
                    }

                    // Set flash message
                    $this->addFlash(
                        'success',
                        $this->getTranslator()->trans(
                            'The changes to %name% are saved',
                            ['%name%' => $contentType->getName()]
                        )
                    );

                    $lock = $this->lockFactory->createLock(self::class);
                    $lock->acquire(true);

                    try {
                        if ($this->indexer instanceof Configurable) {
                            $this->indexer->setOption('queue.size', 2);
                        }

                        $this->indexer->execute(); // lets hope that the gods of random is in our favor as there is no way to guarantee that this will do what we want
                    } finally {
                        $lock->release();
                    }

                    if (!$locking['locked'] && !$this->isTurboStreamRequest($request)) {
                        $locking['release']();
                    }

                    $saved = true;
                }

                if ($this->isTurboStreamRequest($request) && $request->query->getBoolean('frame')) {
                    $isMedia = \in_array($contentType->getClass(), [File::class, Image::class], true);

                    $content = $this->renderView('@IntegratedContent/content/edit.iframe.turbo_stream.html.twig', [
                        'editable' => $this->isGranted(Permissions::EDIT, $content),
                        'taxonomyCategories' => $this->getTaxonomyCategories($content),
                        'type' => $contentType,
                        'form' => $form->createView(),
                        'formRelations' => $this->getFormRelations($form),
                        'content' => $content,
                        'locking' => $locking,
                        'showContentHistory' => true,
                        'references' => json_encode($this->getReferences($content)),
                        'not_shown_filetypes' => array_map('strtolower', MediaController::NOT_SHOWN_FILETYPES),
                        'selected_ids' => [],
                        'is_media' => $isMedia,
                        'saved' => $saved,
                    ]);

                    return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/vnd.turbo-stream.html; charset=UTF-8']);
                }

                if ($this->isTurboStreamRequest($request) && !$request->query->getBoolean('frame')) {
                    $content = $this->renderView('@IntegratedContent/content/edit.status_options.turbo_stream.html.twig', [
                        'content' => $content,
                        'locking' => $locking,
                        'form' => $form->createView(),
                        'publications' => $this->getPublications($content),
                    ]);

                    return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/vnd.turbo-stream.html; charset=UTF-8']);
                }

                return $this->redirectToRoute($request->get('_route'), ['id' => $content->getId()]);
            }
            // reload_changed is just submitting without saving so the changes made are
            // not lost and there is a new change to get a lock on the content.
        }

        if ($locking['locked'] && !$locking['pending']) {
            // the document is locked so display display a error message explaining that
            // the user can not edit this page will the lock is there.

            if ($locking['owner']) {
                $text = 'The document is currently locked by your self in a different browser or tab and can not be edited until this lock is released.';
            } elseif ($locking['user']) {
                $user = $locking['user']->getUsername();

                // we got a basic user name now try to get a better one

                if (method_exists($locking['user'], 'getRelation')) {
                    if ($relation = $locking['user']->getRelation()) {
                        if (method_exists($relation, '__toString')) {
                            $user = (string) $relation;
                        }
                    }
                }

                $text = \sprintf(
                    'The document is currently locked by %s, the document can not be edited until this lock is released.',
                    $user
                );
            } else {
                $text = 'The document is currently locked and can not be edited until this lock is released.';
            }

            $this->addFlash('danger', $text);
        }

        if ($request->query->getBoolean('frame')) {
            $renderTo = '@IntegratedContent/content/edit.iframe.html.twig';
        } elseif ($request->get('_route') == 'integrated_content_content_edit_iframe') {
            $renderTo = '@IntegratedContent/content/edit.iframe.html.twig';
        } elseif ($request->get('_route') == 'integrated_content_content_edit_modal_iframe') {
            $renderTo = '@IntegratedContent/content/edit.modal.iframe.html.twig';
        } else {
            $renderTo = '@IntegratedContent/content/edit.html.twig';
        }

        return $this->render($renderTo, [
            'editable' => $this->isGranted(Permissions::EDIT, $content),
            'taxonomyCategories' => $this->getTaxonomyCategories($content),
            'type' => $contentType,
            'form' => $form,
            'formRelations' => $this->getFormRelations($form),
            'content' => $content,
            'locking' => $locking,
            'publications' => $this->getPublications($content),
            'showContentHistory' => true,
            'references' => json_encode($this->getReferences($content)),
        ]);
    }

    private function isTurboStreamRequest(Request $request): bool
    {
        $accept = (string) $request->headers->get('Accept');

        return str_contains($accept, 'text/vnd.turbo-stream.html');
    }

    /**
     * @param FormInterface<mixed> $form
     *
     * @return array<string, array<string, object>>
     */
    private function getFormRelations(FormInterface $form): array
    {
        $relations = [];

        foreach ($form->getData()->getRelations() as $relation) {
            $references = [];
            foreach ($relation->getReferences() as $imageObject) {
                $references[$imageObject->getId()] = $imageObject;
            }
            $relations[$relation->getRelationId()] = $references;
        }

        return $relations;
    }

    /**
     * Delete a document.
     */
    public function delete(Request $request, Content $content): Response
    {
        /** @var ContentTypeInterface $type */
        $type = $this->resolver->getType($content->getContentType());

        if (!$this->isGranted(Permissions::DELETE, $content)) {
            throw new AccessDeniedException();
        }
        // get a lock on this content resource.

        $prefetchRequest = $this->isPrefetchRequest($request);
        $locking = $prefetchRequest
            ? $this->createUnlockedLocking()
            : $this->getLock($content, self::CONTENT_LOCK_TIMEOUT_SECONDS);
        $locking['locked'] = $prefetchRequest || (bool) $locking['lock'];

        if ($locking['lock'] && $locking['owner']) {
            if ($request->query->has('lock') && $locking['lock']->getId() == $request->query->get('lock')) {
                $locking['locked'] = false;
            }

            if ($locking['new']) {
                if ($request->isMethod('get')) {
                    return $this->redirectToRoute(
                        'integrated_content_content_delete',
                        ['id' => $content->getId(), 'lock' => $locking['lock']->getId()]
                    );
                }

                $locking['locked'] = false;
            }
        }

        $referenced = $this->contentReferenced->getReferenced($content);

        $form = $this->createDeleteForm($content, $locking, \count($referenced) > 0);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // possible actions are cancel, reload and delete
            $submittedAction = $this->resolveSubmittedAction(
                $form->get('actions')->getData(),
                $request,
                ['cancel', 'reload', 'delete']
            );

            if ($submittedAction === 'cancel') {
                if (!$locking['locked']) {
                    $locking['release']();
                }

                return $this->redirectToRoute('integrated_content_content_index', ['remember' => 1]);
            }

            if ($submittedAction === 'reload') {
                $parameters = ['id' => $content->getId()];
                if (!$locking['locked'] && $locking['lock'] && $locking['owner']) {
                    $parameters['lock'] = $locking['lock']->getId();
                }

                return $this->redirectToRoute('integrated_content_content_delete', $parameters);
            }

            // this is not rest compatible since a button click is required to save
            if ($submittedAction === 'delete') {
                if ($form->isValid()) {
                    // higher priority for content edited in Integrated
                    $queue = $this->queueSubscriber->getQueue();
                    $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);

                    if ($this->dispatcher->hasListeners(Events::CONTENT_DELETED)) {
                        $this->dispatcher->dispatch(
                            new ContentDeletedEvent($content),
                            Events::CONTENT_DELETED
                        );
                    }

                    $this->documentManager->remove($content);
                    $this->documentManager->flush();

                    // Set flash message
                    $this->addFlash(
                        'success',
                        $this->getTranslator()->trans(
                            'The document %name% has been deleted',
                            ['%name%' => $type->getName()]
                        )
                    );

                    if ($this->indexer instanceof Configurable) {
                        $this->indexer->setOption('queue.size', 2);
                    }

                    $this->indexer->execute(); // lets hope that the gods of random is in our favor as there is no way to guarantee that this will do what we want

                    if (!$locking['locked']) {
                        $locking['release']();
                    }

                    return $this->redirectToRoute('integrated_content_content_index', ['remember' => 1]);
                }
            }
        }

        if ($locking['locked']) {
            // the document is locked so display display a error message explaining that
            // the user can not edit this page will the lock is there.

            if ($locking['owner']) {
                $text = 'The document is currently locked by your self in a different browser or tab and can not be deleted until this lock is released.';
            } elseif ($locking['user']) {
                $user = $locking['user']->getUsername();

                // we got a basic user name now try to get a better one

                if (method_exists($locking['user'], 'getRelation')) {
                    if ($relation = $locking['user']->getRelation()) {
                        if (method_exists($relation, '__toString')) {
                            $user = (string) $relation;
                        }
                    }
                }

                $text = \sprintf(
                    'The document is currently locked by %s, the document can not be deleted until this lock is released.',
                    $user
                );
            } else {
                $text = 'The document is currently locked and can not be deleted until this lock is released.';
            }

            $this->addFlash('danger', $text);
        }

        return $this->render('@IntegratedContent/content/delete.html.twig', [
            'type' => $type,
            'form' => $form,
            'content' => $content,
            'locking' => $locking,
            'referenced' => $referenced,
        ]);
    }

    /**
     * Get a lock or find out who does have the lock.
     *
     * The result is a array with the following keys:
     * - lock: this will contain the instance of the lock object or null.
     * - user: this is the user the lock belongs to or null if the lock does
     *         not have a owner.
     */
    /**
     * @return array{lock: LockInterface|null, user: mixed, owner: bool, new: bool, pending: bool, release: \Closure}
     */
    private function getLock(object $object, ?int $timeout = null): array
    {
        $locking = $this->getExistingLock($object);
        if ($locking['lock'] || !$locking['pending']) {
            return $locking;
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->createUnlockedLocking();
        }

        $owner = Resource::fromAccount($user);
        $resource = Resource::fromObject($object);

        $lock = $this->acquireLock($resource, $owner, $timeout);
        if (!$lock instanceof LockInterface) {
            return $this->createPendingLocking();
        }

        return [
            'lock' => $lock,
            'user' => $user,
            'owner' => true,
            'new' => true,
            'pending' => false,
            'release' => function () use ($lock): void {
                $this->lockManager->release($lock);
            },
        ];
    }

    private function getLocks(\Traversable $iterator): array
    {
        $results = [];

        $filter = new Filter();
        /** @var ResourceInterface[] $resources */
        $resources = \is_array($filter->resources) ? $filter->resources : [$filter->resources];

        foreach ($iterator as $data) {
            if (!\is_array($data)) {
                continue;
            }
            $type = isset($data['type_class']) ? (string) $data['type_class'] : '';
            $id = isset($data['type_id']) ? (string) $data['type_id'] : '';
            if ($type === '' || $id === '') {
                continue;
            }
            $resources[] = new Resource($type, $id);
        }
        $filter->resources = $resources;

        if (!$filter->resources) {
            return $results;
        }

        $locks = $this->lockManager->findBy($filter) ?? [];
        foreach ($locks as $lock) {
            if ($this->isLockExpired($lock)) {
                continue;
            }

            // get the user the locks belongs to.
            $user = null;

            if ($owner = $lock->getRequest()->getOwner()) {
                if ($this->userManager->getClassName() === $owner->getType()) {
                    $identifier = $owner->getIdentifier();
                    if (\is_string($identifier) && $identifier !== '') {
                        $user = $this->userManager->findByUsername($identifier);
                    }
                }
            }

            $results[$lock->getRequest()->getResource()->getIdentifier()] = [
                'lock' => $lock,
                'user' => $this->resolveLockUserText($user),
            ];
        }

        return $results;
    }

    public function locksStatus(Request $request): JsonResponse
    {
        $resources = $request->request->all('resources');
        if ([] === $resources) {
            $payload = json_decode((string) $request->getContent(), true);
            $resources = \is_array($payload['resources'] ?? null) ? $payload['resources'] : [];
        }

        $filter = new Filter();
        /** @var ResourceInterface[] $filterResources */
        $filterResources = \is_array($filter->resources) ? $filter->resources : [$filter->resources];
        $seen = [];

        foreach ($resources as $resource) {
            if (!\is_array($resource)) {
                continue;
            }

            $type = trim((string) ($resource['type'] ?? ''));
            $id = trim((string) ($resource['id'] ?? ''));

            if ('' === $type || '' === $id) {
                continue;
            }

            $key = $this->getLockResourceKey($type, $id);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $filterResources[] = new Resource($type, $id);
        }
        $filter->resources = $filterResources;

        $locks = [];
        if ($filter->resources) {
            $matches = $this->lockManager->findBy($filter) ?? [];
            foreach ($matches as $lock) {
                if ($this->isLockExpired($lock)) {
                    continue;
                }

                $user = null;

                if ($owner = $lock->getRequest()->getOwner()) {
                    if ($this->userManager->getClassName() === $owner->getType()) {
                        $identifier = $owner->getIdentifier();
                        if (\is_string($identifier) && $identifier !== '') {
                            $user = $this->userManager->findByUsername($identifier);
                        }
                    }
                }

                $resource = $lock->getRequest()->getResource();
                $resourceId = (string) ($resource->getIdentifier() ?? '');
                if ($resourceId === '') {
                    continue;
                }
                $key = $this->getLockResourceKey($resource->getType(), $resourceId);

                $locks[$key] = [
                    'type' => $resource->getType(),
                    'id' => $resourceId,
                    'user' => $this->resolveLockUserText($user),
                ];
            }
        }

        $response = new JsonResponse(['locks' => $locks]);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('max-age', '0');

        return $response;
    }

    public function lock(Request $request, string $id): JsonResponse
    {
        $content = $this->findContentByIdentifier($id);
        if (!$content) {
            throw $this->createNotFoundException('Content not found.');
        }
        $translator = $this->getTranslator();

        if (!$this->isGranted(Permissions::VIEW, $content)) {
            throw new AccessDeniedException();
        }

        if (!$this->isGranted(Permissions::EDIT, $content)) {
            return new JsonResponse([
                'acquired' => false,
                'lock' => null,
                'message' => $translator->trans('You are not allowed to lock this document.'),
            ], Response::HTTP_FORBIDDEN);
        }

        $token = trim((string) $request->request->get('_token', $request->query->get('_token', '')));
        if (!$this->isCsrfTokenValid('integrated_content_lock_'.$content->getId(), $token)) {
            return new JsonResponse([
                'acquired' => false,
                'lock' => null,
                'message' => $translator->trans('The lock request token is invalid. Please reload and try again.'),
            ], Response::HTTP_FORBIDDEN);
        }

        if ($content instanceof File) {
            return new JsonResponse([
                'acquired' => true,
                'lock' => null,
            ]);
        }

        $locking = $this->getLock($content, self::CONTENT_LOCK_TIMEOUT_SECONDS);
        $knownLock = trim((string) $request->request->get('known_lock', $request->query->get('known_lock', '')));

        if ($locking['lock'] && $locking['owner']) {
            $lockId = $locking['lock']->getId();
            if (!$locking['new'] && ('' === $knownLock || $knownLock !== $lockId)) {
                return new JsonResponse([
                    'acquired' => false,
                    'lock' => null,
                    'message' => $translator->trans('The document is currently locked by your self in a different browser or tab and can not be edited until this lock is released.'),
                ], Response::HTTP_LOCKED);
            }

            return new JsonResponse([
                'acquired' => true,
                'lock' => $lockId,
            ]);
        }

        if ($locking['lock']) {
            $user = $this->resolveLockUserText($locking['user'] ?? null);
            $message = $user
                ? \sprintf(
                    $translator->trans('The document is currently locked by %s, the document can not be edited until this lock is released.'),
                    $user
                )
                : $translator->trans('The document is currently locked and can not be edited until this lock is released.');

            return new JsonResponse([
                'acquired' => false,
                'lock' => null,
                'user' => $user,
                'message' => $message,
            ], Response::HTTP_LOCKED);
        }

        return new JsonResponse([
            'acquired' => false,
            'lock' => null,
            'message' => $translator->trans('The lock could not be acquired, please reload and try again.'),
        ], Response::HTTP_CONFLICT);
    }

    public function navdropdowns(Request $request): Response
    {
        $user = $this->getUser();
        $userId = $user instanceof UserInterface ? (string) $user->getId() : 'anonymous';

        $queueStatus = $this->getQueueStatus($request);
        $queuecount = $queueStatus['queuecount'];
        $assignedContent = $this->getAssignedContent();
        $assignedCount = \count($assignedContent);

        $cache = new FilesystemAdapter(self::NAVDROPDOWNS_CACHE_NAMESPACE);
        $cacheItem = $cache->getItem('navdropdowns_'.md5($userId.'|'.$request->getLocale().'|'.$queuecount.'|'.$assignedCount));

        if ($cacheItem->isHit()) {
            return new Response((string) $cacheItem->get());
        }

        $email = '';

        $avatarurl = '//www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?s=45';

        $html = $this->renderView('@IntegratedContent/content/navdropdowns.html.twig', [
            'avatarurl' => $avatarurl,
            'queuecount' => $queuecount,
            'queuepercentage' => $queueStatus['queuepercentage'],
            'assignedContent' => $assignedContent,
        ]);

        $cacheItem->set($html);
        $cacheItem->expiresAfter(86400);
        $cache->save($cacheItem);

        return new Response($html);
    }

    public function assignedStatus(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        $items = array_map(function (array $document): array {
            $contentId = (string) ($document['type_id'] ?? '');
            $title = (string) ($document['title'] ?? '');

            $statusColor = '#f2f2f2';
            $statusIcon = '';

            if (isset($document['workflow_color_string'])) {
                $colors = (array) $document['workflow_color_string'];
                $firstColor = reset($colors);
                if ($firstColor) {
                    $statusColor = (string) $firstColor;
                }
            }

            if (isset($document['workflow_icon_string'])) {
                $icons = (array) $document['workflow_icon_string'];
                $firstIcon = reset($icons);
                if ($firstIcon) {
                    $statusIcon = (string) $firstIcon;
                }
            }

            return [
                'id' => $contentId,
                'title' => $title,
                'status_color' => $statusColor,
                'status_icon' => $statusIcon,
                'edit_url' => $contentId !== '' ? $this->generateUrl('integrated_content_content_edit', ['id' => $contentId]) : '#',
            ];
        }, $this->getAssignedContent());

        $response = new JsonResponse([
            'count' => \count($items),
            'items' => $items,
        ]);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('max-age', '0');

        return $response;
    }

    public function queueStatus(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        $queueStatus = $this->getQueueStatus($request);

        $response = new JsonResponse([
            'queuecount' => $queueStatus['queuecount'],
            'queuepercentage' => $queueStatus['queuepercentage'],
        ]);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('max-age', '0');

        return $response;
    }

    /** @return array{queuecount: int, queuepercentage: int} */
    private function getQueueStatus(Request $request): array
    {
        $queuecount = (int) $this->queueProvider->count();
        $queuepercentage = 100;
        $session = $request->getSession();

        if ($queuecount > 0) {
            $queuemaxcount = max($queuecount, (int) $session->get('queuemaxcount', 0));
            $session->set('queuemaxcount', $queuemaxcount);
            $queuepercentage = (int) round(($queuemaxcount - $queuecount) / $queuemaxcount * 100);
        } else {
            $session->remove('queuemaxcount');
        }

        return [
            'queuecount' => $queuecount,
            'queuepercentage' => $queuepercentage,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function getAssignedContent(): array
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return [];
        }

        $query = $this->getSolarium()->createSelect();

        $query
            ->createFilterQuery('pub_not_active')
            ->setQuery('-pub_active:true');

        $query
            ->createFilterQuery('workflow_assigned_id')
            ->setQuery('facet_workflow_assigned_id:'.$user->getId());

        $result = $this->getSolarium()->select($query);

        $documents = [];
        foreach ($result->getDocuments() as $document) {
            $fields = $document->getFields();
            $documents[] = $fields;
        }

        return $documents;
    }

    private function resolveLockUserText(mixed $user): string
    {
        if (!\is_object($user)) {
            return '';
        }

        $text = method_exists($user, 'getUserIdentifier') ? (string) $user->getUserIdentifier() : '';

        return $text;
    }

    /**
     * @return list<Publication>
     */
    private function getPublications(Content $content): array
    {
        $contentId = $content->getId();
        if (!\is_string($contentId) || '' === $contentId) {
            return [];
        }

        /** @var list<Publication> $publications */
        $publications = $this->documentManager->getRepository(Publication::class)->findBy(['content' => $content]);

        return $publications;
    }

    private function findContentByIdentifier(string $id): ?Content
    {
        /** @var Content|null $content */
        $content = $this->documentManager->getRepository(Content::class)->find($id);
        if ($content) {
            return $content;
        }

        if (preg_match('/^[a-z0-9_]+-([a-f0-9]{24}|[a-f0-9]{32})$/i', $id, $matches)) {
            /** @var Content|null $fallback */
            $fallback = $this->documentManager->getRepository(Content::class)->find($matches[1]);

            return $fallback;
        }

        return null;
    }

    private function isPrefetchRequest(Request $request): bool
    {
        $secPurpose = strtolower(trim((string) $request->headers->get('X-Sec-Purpose', $request->headers->get('Sec-Purpose', ''))));
        if ('prefetch' === $secPurpose) {
            return true;
        }

        $purpose = strtolower(trim((string) $request->headers->get('Purpose', '')));

        return 'prefetch' === $purpose;
    }

    /**
     * @return array{lock: null, user: null, owner: false, new: false, pending: false, release: \Closure}
     */
    private function createUnlockedLocking(): array
    {
        return [
            'lock' => null,
            'user' => null,
            'owner' => false,
            'new' => false,
            'pending' => false,
            'release' => function (): void {
            },
        ];
    }

    /**
     * @return array{lock: null, user: null, owner: false, new: false, pending: true, release: \Closure}
     */
    private function createPendingLocking(): array
    {
        $locking = $this->createUnlockedLocking();
        $locking['pending'] = true;

        return $locking;
    }

    /**
     * @return array{lock: LockInterface|null, user: mixed, owner: bool, new: bool, pending: bool, release: \Closure}
     */
    private function getExistingLock(object $object): array
    {
        if (!$this->isGranted(Permissions::EDIT, $object)) {
            return $this->createUnlockedLocking();
        }

        $resource = Resource::fromObject($object);
        $owner = null;

        if ($user = $this->getUser()) {
            $owner = Resource::fromAccount($user);
        }

        $locksByResource = $this->lockManager->findByResource($resource);
        $ownerLock = $this->resolveOwnerLock($locksByResource, $owner);
        $lock = $ownerLock
            ?: $this->resolveActiveLock($locksByResource);

        if (!$lock && $this->releaseExpiredLocks($locksByResource)) {
            $locksByResource = $this->lockManager->findByResource($resource);
            $ownerLock = $this->resolveOwnerLock($locksByResource, $owner);
            $lock = $ownerLock
                ?: $this->resolveActiveLock($locksByResource);
        }

        if ($ownerLock) {
            $this->releaseDuplicateOwnerLocks($locksByResource, $owner, (string) $ownerLock->getId());
        }

        if ($lock) {
            if ($owner && $owner->equals($lock->getRequest()->getOwner())) {
                return [
                    'lock' => $lock,
                    'user' => $this->getUser(),
                    'owner' => true,
                    'new' => false,
                    'pending' => false,
                    'release' => function () use ($lock): void {
                        $this->lockManager->release($lock);
                    },
                ];
            }

            $user = null;

            if ($lockOwner = $lock->getRequest()->getOwner()) {
                if ($this->userManager->getClassName() === $lockOwner->getType()) {
                    $identifier = $lockOwner->getIdentifier();
                    if (\is_string($identifier) && $identifier !== '') {
                        $user = $this->userManager->findByUsername($identifier);
                    }
                }
            }

            return [
                'lock' => $lock,
                'user' => $user,
                'owner' => false,
                'new' => false,
                'pending' => false,
                'release' => function () use ($lock): void {
                    $this->lockManager->release($lock);
                },
            ];
        }

        return $this->createPendingLocking();
    }

    private function getLockResourceKey(string $type, string $id): string
    {
        return $type.'|'.$id;
    }

    private function acquireLock(ResourceInterface $resource, ResourceInterface $owner, ?int $timeout = null): ?LockInterface
    {
        $request = new Locks\Request($resource);
        $request->setOwner($owner);
        $request->setTimeout($timeout);

        return $this->lockManager->acquire($request);
    }

    /**
     * @param iterable<mixed> $locks
     */
    private function resolveOwnerLock(iterable $locks, ?ResourceInterface $owner): ?LockInterface
    {
        if (!$owner) {
            return null;
        }

        foreach ($locks as $lock) {
            if ($this->isLockExpired($lock)) {
                continue;
            }

            $request = $lock->getRequest();
            $lockOwner = $request->getOwner();
            if ($lockOwner && $owner->equals($lockOwner)) {
                return $lock;
            }
        }

        return null;
    }

    /**
     * @param iterable<mixed> $locks
     */
    private function releaseDuplicateOwnerLocks(iterable $locks, ?ResourceInterface $owner, ?string $keepLockId = null): void
    {
        if (!$owner) {
            return;
        }

        foreach ($locks as $lock) {
            if ($this->isLockExpired($lock)) {
                continue;
            }

            $request = $lock->getRequest();
            $lockOwner = $request->getOwner();
            if (!$lockOwner || !$owner->equals($lockOwner)) {
                continue;
            }

            $lockId = (string) $lock->getId();
            if ($keepLockId !== null && $lockId === $keepLockId) {
                continue;
            }

            $this->lockManager->release($lock);
        }
    }

    /** @param iterable<mixed> $locks */
    private function resolveActiveLock(iterable $locks): ?LockInterface
    {
        foreach ($locks as $lock) {
            if ($this->isLockExpired($lock)) {
                continue;
            }

            return $lock;
        }

        return null;
    }

    /** @param iterable<mixed> $locks */
    private function releaseExpiredLocks(iterable $locks): bool
    {
        $released = false;

        foreach ($locks as $lock) {
            if (!$this->isLockExpired($lock)) {
                continue;
            }

            $this->lockManager->release($lock);
            $released = true;
        }

        return $released;
    }

    private function isLockExpired(LockInterface $lock): bool
    {
        $expires = $lock->getExpires();

        return $expires->getTimestamp() <= time();
    }

    public function usedBy(Content $content, Request $request): Response
    {
        $query = $this->documentManager
            ->createQueryBuilder(Content::class)
            ->field('relations.references.$id')
            ->equals($content->getId())
            ->getQuery();

        $pagination = $this->getPaginator()->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('limit', 15)
        );

        return $this->render('@IntegratedContent/content/used_by.'.$request->getRequestFormat().'.twig', [
            'content' => $content,
            'pagination' => $pagination,
        ]);
    }

    public function mediaTypesAction(?string $filter = null): Response
    {
        return $this->mediaTypes($filter);
    }

    public function mediaTypes(?string $filter = null): Response
    {
        $output = [];

        /* @var Image $image */
        foreach ($this->mediaProvider->getContentTypes($filter) as $contentType) {
            $output[] = [
                'id' => $contentType->getId(),
                'name' => $contentType->getName(),
                'path' => $this->generateUrl(
                    'integrated_content_content_new',
                    ['type' => $contentType->getId(), '_format' => 'iframe.html']
                ),
            ];
        }

        return new JsonResponse($output);
    }

    protected function createNewForm(ContentTypeInterface $contentType, ContentInterface $content, Request $request): FormInterface
    {
        $parameters = array_merge($request->query->all(), [
            'type' => $request->get('type'),
            '_format' => $request->getRequestFormat(),
            'relation' => $request->get('relation'),
        ]);

        $form = $this->createForm(ContentFormType::class, $content, [
            'action' => $this->generateUrl('integrated_content_content_new', $parameters),
            'attr' => [
                'class' => 'content-form',
                'data-content-type' => $contentType->getId(),
            ],
            'content_type' => $contentType,
        ]);

        return $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);
    }

    protected function createEditForm(
        ContentTypeInterface $contentType,
        ContentInterface $content,
        array $locking,
        ?Request $request = null,
    ): FormInterface {
        $hasUsableLock = $locking['lock'] && !$locking['locked'];
        $parameters = ($hasUsableLock ? [
            'id' => $content->getId(),
            'lock' => $locking['lock']->getId(),
        ] : ['id' => $content->getId()]);

        if ($request instanceof Request) {
            $parameters = array_merge($request->query->all(), $parameters);
            if (!$hasUsableLock) {
                unset($parameters['lock']);
            }
        }

        $options = [
            'action' => $this->generateUrl(
                $request->get('_route'),
                $parameters
            ),
            'attr' => [
                'class' => 'content-form',
                'data-content-id' => $content->getId(),
                'data-content-type' => $contentType->getId(),
                'data-lock-id' => $locking['lock'] ? $locking['lock']->getId() : '',
                'data-lock-pending' => $locking['pending'] ? '1' : '0',
                'data-content-locked' => ($locking['locked'] && !$locking['pending']) ? '1' : '0',
                'data-lock-init-url' => $this->generateUrl('integrated_content_content_lock', ['id' => $content->getId()]),
            ],
            'content_type' => $contentType,
        ];

        $pendingLock = $locking['pending'];
        $reloadSubmitted = $request instanceof Request && $this->isSubmittedAction($request, 'reload');

        if ($locking['locked'] && !$pendingLock) {
            // don't display error's when the content is locked as the user can't save in the first place
            $options['validation_groups'] = false;
        }

        $form = $this->createForm(ContentFormType::class, $content, $options);
        $form->add(
            'returnUrl',
            HiddenType::class,
            ['required' => false, 'mapped' => false, 'attr' => ['class' => 'return-url']]
        );

        // load a different set of buttons based on the permissions and locking state

        if (!$this->isGranted(Permissions::EDIT, $content)) {
            return $form->add('actions', ActionsType::class, ['buttons' => ['cancel']]);
        }

        if ($locking['locked'] && !$pendingLock) {
            return $form->add('actions', ActionsType::class, ['buttons' => ['reload', 'cancel']]);
        }

        if ($reloadSubmitted) {
            return $form->add('actions', ActionsType::class, ['buttons' => ['reload', 'save', 'cancel']]);
        }

        return $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);
    }

    private function isSubmittedAction(Request $request, string $action): bool
    {
        foreach ([$request->request, $request->query] as $bag) {
            $payload = $bag->all()['integrated_content'] ?? null;
            if (!\is_array($payload)) {
                continue;
            }

            $actions = $payload['actions'] ?? null;
            if (!\is_array($actions)) {
                continue;
            }

            if (\array_key_exists($action, $actions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $candidates
     */
    private function resolveSubmittedAction(mixed $submittedActionData, Request $request, array $candidates): string
    {
        if (\is_scalar($submittedActionData)) {
            $submittedAction = (string) $submittedActionData;
            if ('' !== $submittedAction && \in_array($submittedAction, $candidates, true)) {
                return $submittedAction;
            }
        }

        foreach ($candidates as $candidate) {
            if ($this->isSubmittedAction($request, $candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    protected function createDeleteForm(ContentInterface $content, array $locking, bool $notDelete = false): FormInterface
    {
        $hasUsableLock = $locking['lock'] && !$locking['locked'];
        $parameters = ['id' => $content->getId()];
        if ($hasUsableLock) {
            $parameters['lock'] = $locking['lock']->getId();
        }

        $form = $this->createForm(DeleteFormType::class, null, [
            'action' => $this->generateUrl(
                'integrated_content_content_delete',
                $parameters
            ),
            'method' => 'DELETE',
        ]);

        // load a different set of buttons based on the locking state
        if ($locking['locked'] || $notDelete) {
            return $form->add('actions', ActionsType::class, ['buttons' => ['reload', 'cancel']]);
        }

        return $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);
    }

    protected function getReferences(ContentInterface $content): array
    {
        $references = [];
        /** @var \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation $relation */
        foreach ($content->getRelations() as $relation) {
            foreach ($relation->getReferences() as $reference) {
                $properties = [
                    'id' => $reference->getId(),
                    'title' => method_exists($reference, '__toString') ? (string) $reference : '',
                ];

                if ($reference instanceof Image) {
                    $properties['image'] = $this->imageExtension->image($reference->getFile())->cropResize(
                        250,
                        250
                    )->jpeg();
                }

                $references[$relation->getRelationId()][] = $properties;
            }
        }

        return $references;
    }
}
