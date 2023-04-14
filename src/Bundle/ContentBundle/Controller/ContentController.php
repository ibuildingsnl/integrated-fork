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
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\ContentBundle\Provider\MediaProvider;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContent;
use Integrated\Bundle\ImageBundle\Twig\Extension\ImageExtension;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexer;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexerInterface;
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
use Integrated\Common\Locks\Provider\DBAL\Manager;
use Integrated\Common\Locks\Resource;
use Integrated\Common\Security\Permissions;
use Integrated\Common\Solr\Indexer\IndexerInterface;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Integrated\MongoDB\Solr\Indexer\QueueSubscriber;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ContentController extends AbstractController
{
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
        private readonly TaxonomyIndexerInterface $taxonomyIndexer,
        private readonly QueryFactoryInterface $queryFactory,
        private readonly MetadataFactoryInterface $metadataFactory,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly DocumentManager $documentManager
    ) {
    }

    public function index(Request $request): Response
    {
        // remember search state
        $session = $request->getSession();

        if ($request->query->get('remember') && $session->has('content_index_view')) {
            $request->query->add(unserialize($session->get('content_index_view')));
            $request->query->remove('remember');
        } elseif (!$request->getRequestFormat() == 'json') {
            $session->set('content_index_view', serialize($request->query->all()));
        }

        $options = $request->query->all();

        // all this relations stuff is only used on the json response
        $relations = [];

        if ($options['relation'] ?? null) {
            $options['contenttypes'] = [];

            if ($relation = $this->getDoctrineODM()->getRepository(Relation::class)->find($options['relation'])) {
                foreach ($relation->getTargets() as $target) {
                    $options['contenttypes'] = $target->getId();
                    $relations[] = [
                        'href' => $this->generateUrl('integrated_content_content_new', ['class' => $target->getClass(), 'type' => $target->getId(), 'relation' => $relation->getId()]),
                        'name' => $target->getName(),
                    ];
                }
            }
        }

        if ($request->isMethod('post')) {
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

        return $this->render('@IntegratedContent/content/index.'.$request->getRequestFormat().'.twig', [
            'params' => $query->getOptions(),
            'pager' => $paginator,
            'facets' => $paginator->getCustomParameters()['result']->getFacetSet()->getFacets(),
            'locks' => $this->getLocks($paginator),
            'relations' => $relations,
        ]);
    }

    /**
     * Show a document.
     *
     * @return Response
     */
    public function show(Request $request, Content $content)
    {
        return $this->render('@IntegratedContent/content/show.'.$request->getRequestFormat().'.twig', [
            'document' => $content,
        ]);
    }

    /**
     * Create a new document.
     *
     * @return Response
     */
    public function new(Request $request)
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
                    $this->dispatcher->dispatch(new ValidationEvent(
                        $contentType,
                        $this->metadataFactory->getMetadata($contentType->getClass()),
                        $content,
                    ), Events::POST_VALIDATE);
                }
                // higher priority for content edited in Integrated
                $queue = $this->queueSubscriber->getQueue();
                $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);

                $this->documentManager->persist($content);
                $this->documentManager->flush();

                $lock = $this->lockFactory->createLock(self::class);
                $lock->acquire(true);

                try {
                    $this->indexer->setOption('queue.size', 2);
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
                $this->addFlash('success', $this->getTranslator()->trans('The document %name% has been created', ['%name%' => $contentType->getName()]));

                return $this->redirectToRoute('integrated_content_content_edit', ['remember' => 1, 'id' => $content->getId()]);
                // TODO: Remember is broken, needs fixin.
            }
        }

        return $this->render(sprintf('@IntegratedContent/content/new.%s.twig', $request->getRequestFormat()), [
            'taxonomyCategories' => $this->getTaxonomyCategories($content),
            'editable' => true,
            'type' => $contentType,
            'form' => $form->createView(),
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

        $taxonomyCategoriess = [];
        foreach ($contentRelations as $contentRelation) {
            foreach ($contentRelation->getTargets() as $target) {
                $taxonomyCategoriess[$contentRelation->getId()] = $this->taxonomyIndexer->buildTaxonomyIndex($target->getId());
            }
        }

        return $taxonomyCategoriess;
    }

    /**
     * Update a existing document.
     *
     * @return Response
     */
    public function edit(Request $request, Content $content)
    {
        /** @var ContentTypeInterface $contentType */
        $contentType = $this->contentTypeManager->getType($content->getContentType());

        if (!$this->isGranted(Permissions::VIEW, $content)) {
            throw new AccessDeniedException('Not granteeedddd');
        }

        $locking = $this->getLock($content, 15);
        $locking['locked'] = (bool) $locking['lock'];

        if (true === $content instanceof File) {
            $locking['locked'] = false;
        } else {
            if ($locking['lock'] && $locking['owner']) {
                if ($request->query->has('lock') && $locking['lock']->getId() == $request->query->get('lock')) {
                    $locking['locked'] = false;
                }

                if ($locking['new']) {
                    if ($request->isMethod('get')) {
                        $parameters = array_merge($request->query->all(), [
                            'id' => $content->getId(),
                            'lock' => $locking['lock']->getId(),
                        ]);

                        return $this->redirectToRoute($request->get('_route'), $parameters);
                    }

                    $locking['locked'] = false;
                }
            }
        }

        $form = $this->createEditForm($contentType, $content, $locking, $request);

        if ($request->isMethod('put')) {
            $form->handleRequest($request);

            // possible actions are cancel, back, reload, reload_changed and save

            if ($form->get('actions')->getData() == 'cancel' || $form->get('actions')->getData() == 'back') {
                if (!$locking['locked']) {
                    $locking['release']();
                }

                $url = $form->get('returnUrl')->getData() ?: $this->generateUrl('integrated_content_content_index', ['remember' => 1]);

                return $this->redirect($url);
            }

            if (!$this->isGranted(Permissions::EDIT, $content)) {
                throw new AccessDeniedException();
            }

            if ($form->get('actions')->getData() == 'reload') {
                return $this->redirectToRoute($request->get('_route'), ['id' => $content->getId()]);
            }

            // this is not rest compatible since a button click is required to save
            if ($form->get('actions')->getData() == 'save') {
                if (!$locking['locked'] && $form->isValid()) {
                    if ($this->dispatcher->hasListeners(Events::POST_VALIDATE)) {
                        $this->dispatcher->dispatch(new ValidationEvent(
                            $contentType,
                            $this->metadataFactory->getMetadata($contentType->getClass()),
                            $content,
                        ), Events::POST_VALIDATE);
                    }

                    // higher priority for content edited in Integrated
                    $queue = $this->queueSubscriber->getQueue();
                    $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);

                    $this->documentManager->flush();

                    // Set flash message
                    $this->addFlash('success', $this->getTranslator()->trans('The changes to %name% are saved', ['%name%' => $contentType->getName()]));

                    $lock = $this->lockFactory->createLock(self::class);
                    $lock->acquire(true);

                    try {
                        $this->indexer->setOption('queue.size', 2);
                        $this->indexer->execute(); // lets hope that the gods of random is in our favor as there is no way to guarantee that this will do what we want
                    } finally {
                        $lock->release();
                    }

                    if (!$locking['locked']) {
                        $locking['release']();
                    }
                }

                return $this->redirectToRoute($request->get('_route'), ['id' => $content->getId()]);
            }
            // reload_changed is just submitting without saving so the changes made are
            // not lost and there is a new change to get a lock on the content.
        }

        if ($locking['locked']) {
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

                $text = sprintf('The document is currently locked by %s, the document can not be edited until this lock is released.', $user);
            } else {
                $text = 'The document is currently locked and can not be edited until this lock is released.';
            }

            $this->addFlash('danger', $text);
        }

        if ($request->get('_route') == 'integrated_content_content_edit_iframe') {
            $renderTo = '@IntegratedContent/content/edit.iframe.html.twig';
        } else {
            $renderTo = '@IntegratedContent/content/edit.html.twig';
        }

        return $this->render($renderTo, [
            'editable' => $this->isGranted(Permissions::EDIT, $content),
            'taxonomyCategories' => $this->getTaxonomyCategories($content),
            'type' => $contentType,
            'form' => $form->createView(),
            'formRelations' => $this->getFormRelations($form),
            'content' => $content,
            'locking' => $locking,
            'showContentHistory' => true,
            'references' => json_encode($this->getReferences($content)),
        ]);
    }

    private function getFormRelations(Form $form): array
    {
        $relations = [];

        foreach ($form->getData()->getRelations()->toArray() as $relation) {
            $references = [];
            foreach ($relation->getReferences()->toArray() as $imageObject) {
                $references[$imageObject->getId()] = $imageObject;
            }
            $relations[$relation->getRelationId()] = $references;
        }

        return $relations;
    }

    /**
     * Delete a document.
     *
     * @return Response
     */
    public function delete(Request $request, Content $content)
    {
        /** @var $type \Integrated\Common\ContentType\ContentTypeInterface */
        $type = $this->resolver->getType($content->getContentType());

        if (!$this->isGranted(Permissions::DELETE, $content)) {
            throw new AccessDeniedException();
        }

        // get a lock on this content resource.

        $locking = $this->getLock($content, 15);
        $locking['locked'] = $locking['lock'] ? true : false;

        if ($locking['lock'] && $locking['owner']) {
            if ($request->query->has('lock') && $locking['lock']->getId() == $request->query->get('lock')) {
                $locking['locked'] = false;
            }

            if ($locking['new']) {
                if ($request->isMethod('get')) {
                    return $this->redirectToRoute('integrated_content_content_delete', ['id' => $content->getId(), 'lock' => $locking['lock']->getId()]);
                }

                $locking['locked'] = false;
            }
        }

        $referenced = $this->contentReferenced->getReferenced($content);

        $form = $this->createDeleteForm($content, $locking, \count($referenced) > 0);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // possible actions are cancel, reload and delete

            if ($form->get('actions')->getData() == 'cancel') {
                if (!$locking['locked']) {
                    $locking['release']();
                }

                return $this->redirectToRoute('integrated_content_content_index', ['remember' => 1]);
            }

            if ($form->get('actions')->getData() == 'reload') {
                return $this->redirectToRoute('integrated_content_content_delete', ['id' => $content->getId()]);
            }

            // this is not rest compatible since a button click is required to save
            if ($form->get('actions')->getData() == 'delete') {
                if ($form->isValid()) {
                    // higher priority for content edited in Integrated
                    $queue = $this->queueSubscriber->getQueue();
                    $this->queueSubscriber->setPriority($queue::PRIORITY_HIGH);

                    $this->documentManager->remove($content);
                    $this->documentManager->flush();

                    // Set flash message
                    $this->addFlash('success', $this->getTranslator()->trans('The document %name% has been deleted', ['%name%' => $type->getName()]));

                    $this->indexer->setOption('queue.size', 2);
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

                $text = sprintf('The document is currently locked by %s, the document can not be deleted until this lock is released.', $user);
            } else {
                $text = 'The document is currently locked and can not be deleted until this lock is released.';
            }

            $this->addFlash('danger', $text);
        }

        return $this->render('@IntegratedContent/content/delete.html.twig', [
            'type' => $type,
            'form' => $form->createView(),
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
     *
     * @param object   $object
     * @param int|null $timeout
     *
     * @return array
     */
    protected function getLock($object, $timeout = null)
    {
        if (!$this->isGranted(Permissions::EDIT, $object)) {
            return [
                'lock' => null,
                'user' => null,
                'owner' => false,
                'new' => false,
                'release' => function () {
                },
            ];
        }

        /** @var Locks\ManagerInterface $service */
        $service = $this->lockManager;

        // Remove expired locks
        $service->clean();

        $object = Resource::fromObject($object);
        $owner = null;

        if ($user = $this->getUser()) {
            $owner = Resource::fromAccount($user);
        }

        if ($owner) {
            $request = new Locks\Request($object);
            $request->setOwner($owner);
            $request->setTimeout($timeout);

            if ($lock = $service->acquire($request)) {
                return [
                    'lock' => $lock,
                    'user' => $this->getUser(),
                    'owner' => true,
                    'new' => true,
                    'release' => function () use ($service, $lock) {
                        $service->release($lock);
                    },
                ];
            }
        } // can not acquire a lock if not logged in.

        if ($lock = $service->findByResource($object)) {
            $lock = $lock[0];

            if ($owner && $owner->equals($lock->getRequest()->getOwner())) {
                return [
                    'lock' => $lock,
                    'user' => $this->getUser(),
                    'owner' => true,
                    'new' => false,
                    'release' => function () use ($service, $lock) {
                        $service->release($lock);
                    },
                ];
            }

            // get the user the locks belongs to.
            $user = null;

            if ($owner = $lock->getRequest()->getOwner()) {
                if ($this->userManager->getClassName() === $owner->getType()) {
                    $user = $this->userManager->findByUsername($owner->getIdentifier());
                }
            }

            return [
                'lock' => $lock,
                'user' => $user,
                'owner' => false,
                'new' => false,
                'release' => function () use ($service, $lock) {
                    $service->release($lock);
                },
            ];
        }

        return [
            'lock' => null,
            'user' => null,
            'owner' => false,
            'new' => false,
            'release' => function () {
            },
        ];
    }

    /**
     * @return array
     */
    protected function getLocks(\Traversable $iterator)
    {
        $results = [];

        $filter = new Filter();

        foreach ($iterator as $data) {
            $filter->resources[] = new Resource($data['type_class'], $data['type_id']);
        }

        if (!$filter->resources) {
            return $results;
        }

        foreach ($this->lockManager->findBy($filter) as $lock) {
            // get the user the locks belongs to.
            $user = null;

            if ($owner = $lock->getRequest()->getOwner()) {
                if ($this->userManager->getClassName() === $owner->getType()) {
                    $user = $this->userManager->findByUsername($owner->getIdentifier());
                }
            }

            $text = '';

            if ($user) {
                $text = $user->getUserIdentifier();

                // we got a basic user name now try to get a better one

                if (method_exists($user, 'getRelation')) {
                    if ($relation = $user->getRelation()) {
                        if (method_exists($relation, '__toString')) {
                            $text = (string) $relation;
                        }
                    }
                }
            }

            $results[$lock->getRequest()->getResource()->getIdentifier()] = [
                'lock' => $lock,
                'user' => $text,
            ];
        }

        return $results;
    }

    /**
     * @return Response
     */
    public function navdropdowns(Request $request)
    {
        $session = $request->getSession();

        $queuecount = (int) $this->container->get('integrated_queue.dbal.provider')->count();
        $queuepercentage = 100;
        if ($queuecount > 0) {
            $queuemaxcount = max($queuecount, $session->get('queuemaxcount'));
            $session->set('queuemaxcount', $queuemaxcount);
            $queuepercentage = round(($queuemaxcount - $queuecount) / $queuemaxcount * 100);
        } else {
            $session->remove('queuemaxcount');
        }

        $email = '';

        $avatarurl = '//www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?s=45';

        /** @var $client \Solarium\Client */
        //
        // Get documents assigned to this user
        //
        $query = $this->getSolarium()->createSelect();

        $assignedContent = [];

        if ($user = $this->getUser()) {
            $userId = $user->getId();

            $query
                ->createFilterQuery('workflow_assigned_id')
                ->setQuery('facet_workflow_assigned_id:'.$userId.'');

            $result = $this->getSolarium()->select($query);

            $assignedContent = $result->getDocuments();
        }

        return $this->render('@IntegratedContent/content/navdropdowns.html.twig', [
            'avatarurl' => $avatarurl,
            'queuecount' => $queuecount,
            'queuepercentage' => $queuepercentage,
            'assignedContent' => $assignedContent,
        ]);
    }

    /**
     * @return Response
     */
    public function usedBy(Content $content, Request $request)
    {
        $qb = $this->documentManager->createQueryBuilder(Content::class);
        $qb->field('relations.references.$id')->equals($content->getId());

        $query = $qb->getQuery();

        /** @var $paginator \Knp\Component\Pager\Paginator */
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

    /**
     * @param null $filter
     *
     * @return JsonResponse
     */
    public function mediaTypesAction($filter = null)
    {
        $output = [];

        /* @var Image $image */
        foreach ($this->mediaProvider->getContentTypes($filter) as $contentType) {
            $output[] = [
                'id' => $contentType->getId(),
                'name' => $contentType->getName(),
                'path' => $this->generateUrl('integrated_content_content_new', ['type' => $contentType->getId(), '_format' => 'iframe.html']),
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * @return FormInterface
     */
    protected function createNewForm(ContentTypeInterface $contentType, ContentInterface $content, Request $request)
    {
        $parameters = array_merge($request->query->all(), [
            'type' => $request->get('type'),
            '_format' => $request->getRequestFormat(),
            'relation' => $request->get('relation'),
        ]);

        $form = $this->createForm(ContentFormType::class, $content, [
            'action' => $this->generateUrl('integrated_content_content_new', $parameters),
            'method' => 'POST',
            'attr' => [
                'class' => 'content-form',
                'data-content-type' => $contentType->getId(),
            ],
            'content_type' => $contentType,
        ]);

        return $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);
    }

    /**
     * @return FormInterface
     */
    protected function createEditForm(ContentTypeInterface $contentType, ContentInterface $content, array $locking, Request $request = null)
    {
        $parameters = ($locking['lock'] ? ['id' => $content->getId(), 'lock' => $locking['lock']->getId()] : ['id' => $content->getId()]);

        if ($request instanceof Request) {
            $parameters = array_merge($request->query->all(), $parameters);
        }

        $options = [
            'action' => $this->generateUrl(
                $request->get('_route'),
                $parameters
            ),
            'method' => 'PUT',
            'attr' => [
                'class' => 'content-form',
                'data-content-id' => $content->getId(),
                'data-content-type' => $contentType->getId(),
            ],
            'content_type' => $contentType,
        ];

        if ($locking['locked']) {
            // don't display error's when the content is locked as the user can't save in the first place
            $options['validation_groups'] = false;
        }

        $form = $this->createForm(ContentFormType::class, $content, $options);
        $form->add('returnUrl', HiddenType::class, ['required' => false, 'mapped' => false, 'attr' => ['class' => 'return-url']]);

        // load a different set of buttons based on the permissions and locking state

        if (!$this->isGranted(Permissions::EDIT, $content)) {
            return $form->add('actions', ActionsType::class, ['buttons' => ['cancel']]);
        }

        if ($locking['locked']) {
            return $form->add('actions', ActionsType::class, ['buttons' => ['reload', 'cancel']]);
        }

        return $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);
    }

    /**
     * @param bool $notDelete
     *
     * @return FormInterface
     */
    protected function createDeleteForm(ContentInterface $content, array $locking, $notDelete = false)
    {
        $form = $this->createForm(DeleteFormType::class, null, [
            'action' => $this->generateUrl('integrated_content_content_delete', $locking['locked'] ? ['id' => $content->getId()] : ['id' => $content->getId(), 'lock' => $locking['lock']->getId()]),
            'method' => 'DELETE',
        ]);

        // load a different set of buttons based on the locking state
        if ($locking['locked'] || $notDelete) {
            return $form->add('actions', ActionsType::class, ['buttons' => ['reload', 'cancel']]);
        }

        return $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);
    }

    /**
     * @return array
     */
    protected function getReferences(ContentInterface $content)
    {
        $references = [];
        /** @var \Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation $relation */
        foreach ($content->getRelations() as $relation) {
            foreach ($relation->getReferences() as $reference) {
                $properties = [
                    'id' => $reference->getId(),
                    'title' => (string) $reference,
                ];

                if ($reference instanceof Image) {
                    $properties['image'] = $this->imageExtension->image($reference->getFile())->cropResize(250, 250)->jpeg();
                }

                $references[$relation->getRelationId()][] = $properties;
            }
        }

        return $references;
    }
}
