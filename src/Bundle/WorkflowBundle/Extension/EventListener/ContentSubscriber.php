<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Extension\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Services\AssignedStatusCacheInvalidator;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\Log;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\Extension\Event\ContentEvent;
use Integrated\Common\Content\Extension\Event\Subscriber\ContentSubscriberInterface;
use Integrated\Common\Content\Extension\Events;
use Integrated\Common\Content\Extension\ExtensionInterface;
use Integrated\Common\Content\MetadataInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Workflow\Event\WorkflowStateChangedEvent;
use Integrated\Common\Workflow\Events as WorkflowEvents;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContentSubscriber implements ContentSubscriberInterface
{
    public const CONTENT_CLASS = 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Relation';
    private const NAVDROPDOWNS_CACHE_NAMESPACE = 'integrated_content_fragments_navdropdowns';

    private ExtensionInterface $extension;

    public function __construct(
        private readonly UserManagerInterface $userManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ResolverInterface $resolver,
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentManager $documentManager,
        private readonly MailerInterface $mailer,
        private readonly RouterInterface $router,
        private readonly ThemeManager $themeManager,
        private readonly AssignedStatusCacheInvalidator $assignedStatusCacheInvalidator,
        private readonly string $fromEmail,
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_READ => 'read',
            Events::PRE_CREATE => 'preUpdate',
            Events::POST_CREATE => 'postUpdate',
            Events::PRE_UPDATE => 'preUpdate',
            Events::POST_UPDATE => 'postUpdate',
            Events::POST_DELETE => 'delete',
        ];
    }

    public function read(ContentEvent $event)
    {
        $content = $event->getContent();

        if (!$this->getWorkflow($content)) {
            return;
        }

        if (!$this->shouldResolveReadState()) {
            $event->setData(null);

            return;
        }

        // check if there is a workflow state for this item else just set
        // everything to empty.

        $data = null;

        if ($state = $this->getStateData($content)) {
            $data = [
                'comment' => '',
                'state' => $state['state'],
                'assigned' => $state['assigned'],
                'deadline' => $state['deadline'],
            ];
        }

        $event->setData($data);
    }

    private function shouldResolveReadState(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return false;
        }

        $route = $request->attributes->get('_route');
        if (!\is_string($route) || '' === $route) {
            return false;
        }

        if (str_starts_with($route, 'integrated_content_content_edit')) {
            return true;
        }

        return \in_array($route, [
            'integrated_content_content_new',
            'integrated_content_content_delete',
            'integrated_content_content_show',
        ], true);
    }

    public function preUpdate(ContentEvent $event)
    {
        $content = $event->getContent();

        if (!$workflow = $this->getWorkflow($content)) {
            return;
        }

        $data = \is_array($data = $event->getData()) ? array_filter($data) : []; // filter out empty fields
        $data += [
            'comment' => '',
            'state' => ($state = $this->getState($content)) ? $state->getState() : null,
            'assigned' => null,
            'deadline' => null,
        ];

        // if there still is no state then load / force the default state

        if (!$data['state']) {
            $data['state'] = $workflow->getDefault();
        }

        if (!$data['assigned'] instanceof User && $data['assigned']) {
            $data['assigned'] = $this->userManager->findOneBy(['id' => $data['assigned']]);
        }

        if ($data['assigned'] && !$this->hasAssignedAccess($data['assigned'], $data['state'], $content)) {
            $data['assigned'] = null;
        }

        $event->setData($data);

        /**
         * @var Definition\State
         */
        $state = $data['state'];

        try {
            $state->getWorkflow();
        } catch (\Exception $e) {
            error_log('Failed to get workflow: '.$e->getMessage());
            $state = false;
            // TODO: Entry should be removed from workflow_states table
        }

        if ($content instanceof MetadataInterface) {
            if ($state) {
                $content->getMetadata()->set('workflow', $state->getWorkflow()?->getId());
                $content->getMetadata()->set('workflow_state', $state->getId());

                // hax: setDisabled is not in the interface
                if (method_exists($content, 'setDisabled')) {
                    $content->setDisabled(!$state->isPublishable());
                }
            } else {
                $content->getMetadata()->remove('workflow');
                $content->getMetadata()->remove('workflow_state');
            }
        }
    }

    public function postUpdate(ContentEvent $event)
    {
        $content = $event->getContent();

        if (!$this->getWorkflow($content)) {
            return;
        }

        $data = $event->getData();

        if (!$state = $this->getState($content)) {
            $state = new State();
            $state->setContent($content);

            $this->entityManager->persist($state);
        }

        $persist = false;
        $assignedChanged = false;
        $assignedStatusUserIds = $this->getAssignedStatusUserIds($state->getAssigned());

        $log = new Log();
        $log->setUser($this->getUser());

        if ($data['comment']) {
            $log->setComment($data['comment']);

            $persist = true;
        }

        // log the old settings if changed

        if ($data['state'] !== $state->getState()) {
            $log->setState($data['state']);
            $state->setState($data['state']);

            $this->eventDispatcher->dispatch(new WorkflowStateChangedEvent($state, $content), WorkflowEvents::STATE_CHANGED);

            $persist = true;
        }

        if ($data['assigned'] !== $state->getAssigned()) {
            $state->setAssigned($data['assigned']);
            $assignedChanged = true;
            $assignedStatusUserIds = array_merge($assignedStatusUserIds, $this->getAssignedStatusUserIds($data['assigned']));

            // sent mail when user changed

            if ($data['assigned'] instanceof User) {
                if ($data['assigned']->getRelation() instanceof Person) {
                    $person = $data['assigned']->getRelation();

                    if ($person->getEmail()) {
                        $title = 'unknown';
                        if (method_exists($content, 'getTitle')) {
                            $title = $content->getTitle();
                        } elseif (method_exists($content, 'getName')) {
                            $title = $content->getName();
                        }

                        $link = $this->router->generate('integrated_content_content_edit', ['id' => $content->getId()]);

                        $baseUrl = (($content instanceof Content) ? $content->getPrimaryChannel()?->getPrimaryDomain() : null) ??
                                   ((isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]");

                        $template = $this->themeManager->locateTemplate('mail/workflow-notification.html.twig');

                        $lines = [
                            'A new item has been assigned to you:',
                            "Document: <a href=\"{$baseUrl}{$link}\">{$title}</a>",
                            "E-mail: {$person->getEmail()}",
                            $data['deadline'] ?? false ? "Deadline: {$data['deadline']->format('d-m-Y H:i:s')}" : '',
                        ];

                        $email['emailContent'] = implode('<br>', $lines);
                        $email['subject'] = 'A new item has been assigned to you: "'.$title.'"';

                        try {
                            $message = (new TemplatedEmail())
                                ->from($this->fromEmail)
                                ->to($person->getEmail())
                                ->htmlTemplate($this->themeManager->locateTemplate('mail/workflow-notification.html.twig'))
                                ->subject($email['subject'])
                                ->context($email);
                            $this->mailer->send($message);
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }

        $deadline = $this->normalizeDeadline($data['deadline'] ?? null);

        if ($this->hasDeadlineChanged($deadline, $state->getDeadline())) {
            $log->setDeadline($deadline);
            $state->setDeadline($deadline);

            $persist = true;
        }

        if ($persist) {
            $this->entityManager->persist($log);

            $state->addLog($log);
        }

        $this->entityManager->flush();

        $this->assignedStatusCacheInvalidator->invalidateUsers($assignedStatusUserIds);

        if ($persist || $assignedChanged) {
            $this->invalidateNavdropdownCache();
        }
    }

    public function delete(ContentEvent $event)
    {
        $content = $event->getContent();

        if (!$this->getWorkflow($content)) {
            return;
        }

        if ($state = $this->getState($content)) {
            $this->entityManager->remove($state);
        }

        $event->setData(null);

        if ($content instanceof MetadataInterface) {
            $content->getMetadata()->remove('workflow');
            $content->getMetadata()->remove('workflow_state');
        }
    }

    protected function getState(ContentInterface $content): ?State
    {
        $repository = $this->entityManager->getRepository(State::class);

        if ($entity = $repository->findOneBy(['content' => $content])) {
            return $entity;
        }

        return null;
    }

    /**
     * Lightweight read path to avoid hydrating thousands of Workflow\State ORM entities.
     *
     * @return array{state: Definition\State, assigned: string|null, deadline: \DateTimeInterface|null}|null
     */
    protected function getStateData(ContentInterface $content): ?array
    {
        $contentId = $content->getId();
        if (!\is_scalar($contentId) || '' === (string) $contentId) {
            return null;
        }

        $cacheKey = ClassUtils::getRealClass($content).'#'.(string) $contentId;
        static $cache = [];

        if (\array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $row = $this->entityManager->getConnection()->fetchAssociative(
            'SELECT state_id, assigned_id, assigned_class, deadline FROM workflow_states WHERE content_id = :content_id AND content_class = :content_class LIMIT 1',
            [
                'content_id' => (string) $contentId,
                'content_class' => ClassUtils::getRealClass($content),
            ]
        );

        if (!\is_array($row) || !isset($row['state_id'])) {
            return $cache[$cacheKey] = null;
        }

        $stateId = (string) $row['state_id'];
        if ('' === $stateId) {
            return $cache[$cacheKey] = null;
        }

        $state = $this->entityManager->getRepository(Definition\State::class)->find($stateId);
        if (!$state instanceof Definition\State) {
            return $cache[$cacheKey] = null;
        }

        $assigned = null;
        $assignedClass = isset($row['assigned_class']) && \is_string($row['assigned_class']) ? $row['assigned_class'] : null;
        if ($assignedClass && is_a($assignedClass, User::class, true)) {
            $assigned = isset($row['assigned_id']) ? (string) $row['assigned_id'] : null;
        }

        $deadline = null;
        if (isset($row['deadline']) && \is_string($row['deadline']) && '' !== $row['deadline']) {
            try {
                $deadline = new \DateTimeImmutable($row['deadline']);
            } catch (\Throwable $e) {
                $deadline = null;
            }
        }

        return $cache[$cacheKey] = [
            'state' => $state,
            'assigned' => $assigned,
            'deadline' => $deadline,
        ];
    }

    protected function getUser(): ?UserInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

    protected function getWorkflow(object $object): ?Definition
    {
        if (!$object instanceof ContentInterface) {
            return null;
        }

        // resolve the object to a content type and check if it got a workflow connected.

        $type = $object->getContentType();

        if (!$type) {
            return null;
        }

        if (!$this->resolver->hasType($type)) {
            return null;
        }

        $type = $this->resolver->getType($type);

        if ($workflow = $type->getOption('workflow')) {
            $repository = $this->entityManager->getRepository(Definition::class);

            if ($entity = $repository->find($workflow)) {
                return $entity;
            }
        }

        return null;
    }

    public function setExtension(ExtensionInterface $extension): void
    {
        $this->extension = $extension;
    }

    public function getExtension(): ExtensionInterface
    {
        return $this->extension;
    }

    protected function hasAssignedAccess(User $assigned, Definition\State $state, ContentInterface $content): bool
    {
        $groups = [];
        /** @var Group $group */
        foreach ($assigned->getGroups() as $group) {
            $groups[] = $group->getId();
        }

        if (\count($state->getPermissions()) > 0) {
            $permissionObject = $state;
        } else {
            // permissions inherited from content type
            $contentType = $this->documentManager->getRepository(ContentType::class)->find($content->getContentType());
            if ($contentType) {
                if (\count($contentType->getPermissions()) == 0) {
                    return true;
                }
                $permissionObject = $contentType;
            } else {
                return false;
            }
        }

        foreach ($permissionObject->getPermissions() as $permission) {
            if (\in_array($permission->getGroup(), $groups) && $permission->getMask() >= PermissionInterface::WRITE) {
                return true;
            }
        }

        return false;
    }

    private function normalizeDeadline(mixed $deadline): ?\DateTime
    {
        if (!$deadline instanceof \DateTimeInterface) {
            return null;
        }

        return \DateTime::createFromInterface($deadline);
    }

    private function hasDeadlineChanged(?\DateTimeInterface $submittedDeadline, ?\DateTimeInterface $currentDeadline): bool
    {
        if ($submittedDeadline === null || $currentDeadline === null) {
            return $submittedDeadline !== $currentDeadline;
        }

        return $submittedDeadline->format('U.u') !== $currentDeadline->format('U.u');
    }

    private function invalidateNavdropdownCache(): void
    {
        (new FilesystemAdapter(self::NAVDROPDOWNS_CACHE_NAMESPACE))->clear();
    }

    /**
     * @return array<int, string|null>
     */
    private function getAssignedStatusUserIds(mixed $assigned): array
    {
        if (!$assigned instanceof UserInterface) {
            return [];
        }

        return [(string) $assigned->getId()];
    }
}
