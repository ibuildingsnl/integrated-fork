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
use Integrated\Bundle\ContentBundle\Form\Type\ChannelType;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type as Form;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelController extends AbstractController
{
    private DocumentManager $documentManager;
    private SearchContentReferenced $searchContentReferenced;
    private EventDispatcherInterface $dispatcher;

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
            'form' => $form,
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
            return $this->redirectToRoute('integrated_content_channel_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->documentManager->persist($channel);
            $this->documentManager->flush();

            $this->addFlash('success', 'Item created');

            $this->dispatcher->dispatch(new ChannelEvent($channel), Events::CHANNEL_CREATED);

            return $this->redirectToRoute('integrated_content_channel_edit', ['id' => $channel->getId()]);
        }

        return $this->render('@IntegratedContent/channel/new.html.twig', [
            'form' => $form,
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
            'form' => $form,
            'channel' => $channel,
        ]);
    }

    public function delete(Request $request, Channel $channel): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $referenced = $this->searchContentReferenced->getReferenced($channel);

        /** @var Form $form */
        $form = $this->createDeleteForm($channel->getId(), \count($referenced) === 0);
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_channel_index');
        }

        if ($form->isSubmitted() && $form->isValid() && $form->getClickedButton()?->getName() === 'submit') {
            $this->documentManager->remove($channel);
            $this->documentManager->flush();

            $this->dispatcher->dispatch(new ChannelEvent($channel), Events::CHANNEL_DELETED);

            $this->addFlash('success', 'Channel deleted');

            return $this->redirectToRoute('integrated_content_channel_index');
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
        $channels = $this->documentManager->getRepository(Channel::class)->findBy([], ['name' => 1]);

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
