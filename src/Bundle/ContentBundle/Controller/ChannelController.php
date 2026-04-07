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
use Integrated\Bundle\BlockBundle\Service\RuntimeBlockUsageCollector;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type\ChannelType;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events as ChannelEvents;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ChannelController extends AbstractController
{
    private const CHANNELS_CACHE_NAMESPACE = 'integrated_content_fragments_channels';
    private const CHANNELS_CACHE_TTL_SECONDS = 86400;

    private DocumentManager $documentManager;
    private SearchContentReferenced $searchContentReferenced;
    private EventDispatcherInterface $dispatcher;
    private QueueInterface $workflowQueue;
    private RuntimeBlockUsageCollector $runtimeBlockUsageCollector;
    private RequestStack $requestStack;

    public function __construct(
        DocumentManager $documentManager,
        SearchContentReferenced $searchContentReferenced,
        EventDispatcherInterface $dispatcher,
        QueueInterface $workflowQueue,
        RuntimeBlockUsageCollector $runtimeBlockUsageCollector,
        RequestStack $requestStack,
    ) {
        $this->searchContentReferenced = $searchContentReferenced;
        $this->documentManager = $documentManager;
        $this->dispatcher = $dispatcher;
        $this->workflowQueue = $workflowQueue;
        $this->runtimeBlockUsageCollector = $runtimeBlockUsageCollector;
        $this->requestStack = $requestStack;
    }

    public function index(): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $documents = $this->documentManager->getRepository(Channel::class)->findBy([], ['name' => 1]);

        return $this->render('@IntegratedContent/channel/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    public function show(Channel $channel): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@IntegratedContent/channel/show.html.twig', [
            'channel' => $channel,
        ]);
    }

    public function new(Request $request): Response|RedirectResponse
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $channel = new Channel();

        $form = $this->createCreateForm($channel);
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->persist($channel);
            $this->documentManager->flush();

            $this->addFlash('success', 'Item created');

            $this->dispatcher->dispatch(new ChannelEvent($channel), ChannelEvents::CHANNEL_CREATED);

            return $this->redirectToRoute('integrated_content_channel_edit', ['id' => $channel->getId()]);
        }

        return $this->render('@IntegratedContent/channel/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Channel $channel, Request $request): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createEditForm($channel);
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_channel_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->flush();

            $this->addFlash('success', 'Item updated');

            $this->dispatcher->dispatch(new ChannelEvent($channel), ChannelEvents::CHANNEL_UPDATED);

            return $this->redirectToRoute('integrated_content_channel_edit', ['id' => $channel->getId()]);
        }

        return $this->render('@IntegratedContent/channel/edit.html.twig', [
            'form' => $form,
            'channel' => $channel,
        ]);
    }

    public function delete(Request $request, string $id): Response
    {
        $channel = $this->documentManager->getRepository(Channel::class)->find($id);
        if (!$channel) {
            return $this->redirectToRoute('integrated_content_channel_index');
        }

        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $referenced = $this->searchContentReferenced->getReferenced($channel);
        $referencedDocuments = $this->searchContentReferenced->getReferencedDocuments($channel);

        $form = $this->createDeleteForm($channel->getId(), \count($referenced) === 0);
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_channel_index');
        }

        if ($form->isSubmitted() && $form->isValid() && $form->get('actions')->getData() == 'delete') {
            if (\count($referencedDocuments) > 0) {
                $deleteReferenced = $form->has('delete_referenced') && $form->get('delete_referenced')->getData();
                if (!$deleteReferenced) {
                    $this->addFlash('warning', 'This channel has related pages. Select "Delete related pages" to proceed.');

                    if ($this->isTurboStreamRequest($request)) {
                        $content = $this->renderView('@IntegratedContent/channel/delete_form.turbo_stream.html.twig', [
                            'channel' => $channel,
                            'form' => $form->createView(),
                            'referenced' => $referenced,
                        ]);

                        return new Response($content, Response::HTTP_OK, [
                            'Content-Type' => 'text/vnd.turbo-stream.html; charset=UTF-8',
                        ]);
                    }

                    return $this->render('@IntegratedContent/channel/delete.html.twig', [
                        'channel' => $channel,
                        'form' => $form->createView(),
                        'referenced' => $referenced,
                    ]);
                }
            }
            $this->workflowQueue->push([
                'command' => 'channel-delete',
                'args' => [
                    'channel_id' => (string) $channel->getId(),
                    'delete_referenced' => true,
                ],
            ]);

            $this->addFlash('success', 'Channel deletion has been queued and will be processed in the background.');

            return $this->redirectToRoute('integrated_content_channel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@IntegratedContent/channel/delete.html.twig', [
            'channel' => $channel,
            'form' => $form,
            'referenced' => $referenced,
        ]);
    }

    protected function createCreateForm(Channel $channel): FormInterface
    {
        $form = $this->createForm(ChannelType::class, $channel, [
            'action' => $this->generateUrl('integrated_content_channel_new'),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    protected function createEditForm(Channel $channel): FormInterface
    {
        $form = $this->createForm(ChannelType::class, $channel, [
            'action' => $this->generateUrl('integrated_content_channel_edit', ['id' => $channel->getId()]),
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    /**
     * @param mixed $id The document id
     */
    /** @return FormInterface<mixed> */
    protected function createDeleteForm(string $id, bool $deleteAllowed): FormInterface
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('integrated_content_channel_delete', ['id' => $id, '_format' => 'turbo-stream']))
            ->setMethod('DELETE');
        if (!$deleteAllowed) {
            $form->add('delete_referenced', CheckboxType::class, [
                'required' => false,
                'label' => 'Delete related pages',
                'label_attr' => ['class' => 'control-label'],
                'attr' => ['class' => 'form-control'],
            ]);
        }

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form->getForm();
    }

    private function isTurboStreamRequest(Request $request): bool
    {
        $accept = $request->headers->get('Accept', '');

        return str_contains($accept, 'text/vnd.turbo-stream.html')
            || $request->getRequestFormat() === 'turbo-stream';
    }

    public function getChannels(Request $request): Response
    {
        $showBlocks = $this->shouldShowBlocks($request);
        $usedBlocks = $showBlocks
            ? $this->normalizeUsedBlocks($request->attributes->get('usedBlocks', $this->runtimeBlockUsageCollector->all()))
            : [];

        $user = $this->getUser();
        $userId = $user instanceof UserInterface ? (string) $user->getId() : 'anonymous';
        $sessionId = $request->hasSession() ? (string) $request->getSession()->getId() : '';
        $cacheKey = 'channels_'.md5(json_encode([
            'user' => $userId,
            'locale' => $request->getLocale(),
            'session' => $sessionId,
            'showBlocks' => $showBlocks,
            'usedBlocks' => $usedBlocks,
        ], \JSON_THROW_ON_ERROR));

        $cache = new FilesystemAdapter(self::CHANNELS_CACHE_NAMESPACE);
        $cacheItem = $cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return new Response((string) $cacheItem->get());
        }

        if (!$user instanceof UserInterface) {
            $html = $this->renderView('@IntegratedContent/partials/block.websites.html.twig', [
                'channels' => [],
                'showBlocks' => $showBlocks,
                'usedBlocks' => $usedBlocks,
            ]);

            $cacheItem->set($html);
            $cacheItem->expiresAfter(self::CHANNELS_CACHE_TTL_SECONDS);
            $cache->save($cacheItem);

            return new Response($html);
        }

        $html = $this->renderView('@IntegratedContent/partials/block.websites.html.twig', [
            'channels' => $this->getAllowedChannels($user),
            'showBlocks' => $showBlocks,
            'usedBlocks' => $usedBlocks,
        ]);

        $cacheItem->set($html);
        $cacheItem->expiresAfter(self::CHANNELS_CACHE_TTL_SECONDS);
        $cache->save($cacheItem);

        return new Response($html);
    }

    /**
     * @return Channel[]
     */
    private function getAllowedChannels(UserInterface $user): array
    {
        $channels = $this->documentManager->getRepository(Channel::class)->findBy([], ['name' => 1]);
        $allowed = [];

        foreach ($channels as $channel) {
            $permissions = PermissionResolver::getPermissions($user, $channel->getPermissions());

            if ($permissions['read'] === true || $permissions['write'] === true) {
                $allowed[] = $channel;
            }
        }

        return $allowed;
    }

    /**
     * @return array<int, array{id: string, title: string, type: string}>
     */
    private function normalizeUsedBlocks(mixed $blocks): array
    {
        if (!\is_array($blocks)) {
            return [];
        }

        $normalized = [];

        foreach ($blocks as $block) {
            if (!\is_array($block) || !\array_key_exists('id', $block)) {
                continue;
            }

            $id = trim((string) $block['id']);
            if ($id === '') {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'title' => trim((string) ($block['title'] ?? '')),
                'type' => trim((string) ($block['type'] ?? '')),
            ];
        }

        return $normalized;
    }

    private function shouldShowBlocks(Request $request): bool
    {
        $canManagePages = $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_WEBSITE_MANAGER');
        if (!$canManagePages) {
            return false;
        }

        if ((bool) $request->attributes->get('showBlocks', false)) {
            return true;
        }

        foreach ([$request, $this->requestStack->getMainRequest()] as $candidate) {
            if (!$candidate instanceof Request) {
                continue;
            }

            $editorMode = strtolower(trim((string) $candidate->query->get('integrated_website_edit', '')));
            if (\in_array($editorMode, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }
        }

        return false;
    }
}
