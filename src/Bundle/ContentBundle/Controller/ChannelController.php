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

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelFactoryInterface;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type as Form;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Integrated\Common\Services\Flusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for CRUD actions Channel document.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ChannelController extends AbstractController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly SearchContentReferenced $searchContentReferenced,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ChannelFactoryInterface $channelFactory,
        private readonly Flusher $flusher,
    ) {
    }

    public function index(): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $documents = $this->channelRepository->findBy([], ['name' => 1]);

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

        $channel = $this->channelFactory->create('website');

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

        $channel = $this->channelFactory->create('website');

        $form = $this->createCreateForm($channel);
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_channel_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->channelRepository->add($channel);
            $this->flusher->flush();

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
            $this->flusher->flush();

            $this->addFlash('success', 'Item updated');

            $this->dispatcher->dispatch(new ChannelEvent($channel), Events::CHANNEL_UPDATED);

            return $this->redirectToRoute('integrated_content_channel_edit', ['id' => $channel->getId()]);
        }

        return $this->render('@IntegratedContent/channel/edit.html.twig', [
            'form' => $form->createView(),
            'channel' => $channel,
        ]);
    }

    public function delete(Request $request, Channel $channel): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $referenced = $this->searchContentReferenced->getReferenced($channel);

        $form = $this->createDeleteForm($channel->getId(), \count($referenced) === 0);
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_channel_index');
        }

        if ($form->isSubmitted() && $form->isValid() && $form->has('submit') && $form->get('submit')->isClicked()) {
            $this->channelRepository->remove($channel);
            $this->flusher->flush();

            $this->dispatcher->dispatch(new ChannelEvent($channel), Events::CHANNEL_DELETED);

            $this->addFlash('success', 'Channel deleted');

            return $this->redirectToRoute('integrated_content_channel_index');
        }

        return $this->render('@IntegratedContent/channel/delete.html.twig', [
            'channel' => $channel,
            'form' => $form->createView(),
            'referenced' => $referenced,
        ]);
    }

    protected function createCreateForm(ChannelInterface $channel): FormInterface
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

    protected function createEditForm(ChannelInterface $channel): FormInterface
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
            ->setAction($this->generateUrl('integrated_content_channel_delete', ['id' => $id]))
            ->setMethod('DELETE');
        if ($deleteAllowed) {
            $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);
        } else {
            $form->add('actions', ActionsType::class, ['buttons' => ['reload', 'cancel']]);
        }

        return $form->getForm();
    }

    public function getchannels(): Response
    {
        $channels = $this->channelRepository->findBy([], ['name' => 1]);

        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            return $this->render('@IntegratedContent/partials/block.websites.html.twig', [
                'channels' => [],
            ]);
        }

        $allowedChannels = [];

        foreach ($channels as $channel) {
            $permissions = PermissionResolver::getPermissions($user, $channel->getPermissions());

            if ($permissions['read'] === true || $permissions['write'] === true) {
                $allowedChannels[] = $channel;
            }
        }

        return $this->render('@IntegratedContent/partials/block.websites.html.twig', [
            'channels' => $allowedChannels,
        ]);
    }
}
