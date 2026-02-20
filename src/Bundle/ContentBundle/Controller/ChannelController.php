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
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Event\ContentDeletedEvent;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type as Form;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events as ChannelEvents;
use Integrated\Common\Content\Form\Events as ContentEvents;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Turbo\TurboStreamResponse;

/**
 * Controller for CRUD actions Channel document.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ChannelController extends AbstractController
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var SearchContentReferenced
     */
    protected $searchContentReferenced;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(
        DocumentManager $documentManager,
        SearchContentReferenced $searchContentReferenced,
        EventDispatcherInterface $dispatcher
    ) {
        $this->searchContentReferenced = $searchContentReferenced;
        $this->documentManager = $documentManager;
        $this->dispatcher = $dispatcher;
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

    public function new(): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $channel = new Channel();

        $form = $this->createCreateForm($channel);

        return $this->render('@IntegratedContent/channel/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
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

            $this->dispatcher->dispatch(new ChannelEvent($channel), Events::CHANNEL_CREATED);

            return $this->redirectToRoute('integrated_content_channel_edit', ['id' => $channel->getId()]);
        }

        return $this->render('@IntegratedContent/channel/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Channel $channel): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createEditForm($channel);

        return $this->render('@IntegratedContent/channel/edit.html.twig', [
            'form' => $form->createView(),
            'channel' => $channel,
        ]);
    }

    public function update(Request $request, Channel $channel): Response|RedirectResponse
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

            $this->dispatcher->dispatch(new ChannelEvent($channel), Events::CHANNEL_UPDATED);

            return $this->redirectToRoute('integrated_content_channel_edit', ['id' => $channel->getId()]);
        }

        return $this->render('@IntegratedContent/channel/edit.html.twig', [
            'form' => $form->createView(),
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

                        return new TurboStreamResponse($content);
                    }

                    return $this->render('@IntegratedContent/channel/delete.html.twig', [
                        'channel' => $channel,
                        'form' => $form->createView(),
                        'referenced' => $referenced,
                    ]);
                }

                foreach ($referencedDocuments as $document) {
                    if ($document instanceof Content) {
                        if ($this->dispatcher->hasListeners(ContentEvents::CONTENT_DELETED)) {
                            $this->dispatcher->dispatch(
                                new ContentDeletedEvent($document),
                                ContentEvents::CONTENT_DELETED
                            );
                        }
                        $this->documentManager->remove($document);
                        continue;
                    }

                    if ($document instanceof AbstractPage) {
                        $this->documentManager->remove($document);
                        continue;
                    }

                    if ($document instanceof ChannelLink) {
                        $document->channel = null;
                        $this->documentManager->persist($document);
                        continue;
                    }

                    if (method_exists($document, 'removeChannel')) {
                        $document->removeChannel($channel);
                        if (method_exists($document, 'getPrimaryChannel') && method_exists($document, 'setPrimaryChannel')) {
                            $primary = $document->getPrimaryChannel();
                            if ($primary && $primary->getId() === $channel->getId()) {
                                $document->setPrimaryChannel(null);
                            }
                        }
                        $this->documentManager->persist($document);
                    }
                }
            }

            $publications = $this->documentManager->getRepository(Publication::class)
                ->createQueryBuilder()
                ->field('channel.$id')
                ->equals($channel->getId())
                ->getQuery()
                ->toArray();

            foreach ($publications as $publication) {
                $this->documentManager->remove($publication);
            }

            $this->documentManager->remove($channel);
            $this->documentManager->flush();

            $this->dispatcher->dispatch(new ChannelEvent($channel), ChannelEvents::CHANNEL_DELETED);

            $this->addFlash('success', 'Channel deleted');

            if ($this->isTurboStreamRequest($request)) {
                $content = $this->renderView('@IntegratedContent/channel/delete.turbo_stream.html.twig');

                return new TurboStreamResponse($content);
            }

            return $this->redirectToRoute('integrated_content_channel_index');
        }

        return $this->render('@IntegratedContent/channel/delete.html.twig', [
            'channel' => $channel,
            'form' => $form->createView(),
            'referenced' => $referenced,
        ]);
    }

    protected function createCreateForm(Channel $channel): FormInterface
    {
        $form = $this->createForm(
            Form\ChannelType::class,
            $channel,
            [
                'action' => $this->generateUrl('integrated_content_channel_create'),
                'method' => 'POST',
            ]
        );

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    protected function createEditForm(Channel $channel): FormInterface
    {
        $form = $this->createForm(Form\ChannelType::class, $channel, [
            'action' => $this->generateUrl('integrated_content_channel_update', ['id' => $channel->getId()]),
            'method' => 'PUT',
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    /**
     * @param mixed $id The document id
     */
    protected function createDeleteForm($id, bool $deleteAllowed): FormInterface
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

    public function getChannels(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            return $this->render('@IntegratedContent/partials/block.websites.html.twig', [
                'channels' => [],
            ]);
        }

        return $this->render('@IntegratedContent/partials/block.websites.html.twig', [
            'channels' => $this->getAllowedChannels($user),
        ]);
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
}
