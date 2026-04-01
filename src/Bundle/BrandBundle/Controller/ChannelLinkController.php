<?php

namespace Integrated\Bundle\BrandBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Event\BrandUpdatedEvent;
use Integrated\Bundle\BrandBundle\Form\Type\ChannelLinkEditType;
use Integrated\Bundle\BrandBundle\Form\Type\ChannelLinkType;
use Integrated\Bundle\BrandBundle\Provider\AvailableConnectorsProvider;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Infrastructure\ChannelTypeRegistry;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Integrated\Common\Services\Flusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelLinkController extends AbstractController
{
    public function __construct(
        private readonly ChannelTypeRegistry $channelTypeRegistry,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Flusher $flusher,
        private readonly DocumentManager $dm,
        private readonly AvailableConnectorsProvider $availableConnectorsProvider,
    ) {
    }

    public function addChannel(Request $request, Brand $brand, string $type): Response
    {
        $this->checkPermissions();

        $channelType = $this->channelTypeRegistry->getType($type);
        if (!$channelType instanceof ChannelType) {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        $channel = new Channel();
        $channel->setType($channelType);
        $defaultChannelName = \sprintf('%s %s', $brand->getName(), $channelType->getName());
        $channel->setName($defaultChannelName);

        $link = new ChannelLink($channelType, $channel, false);

        $form = $this->createForm(ChannelLinkType::class, $link, [
            'method' => 'POST',
            'brand_name' => $brand->getName(),
            'allow_choose' => true,
            'channel_name_locked' => true,
            'channel_default_name' => $defaultChannelName,
        ]);
        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$link->channel instanceof Channel) {
                $form->addError(new FormError('Please select an existing channel.'));

                return $this->render('@IntegratedBrand/brand/channel_add.html.twig', [
                    'linkType' => $channelType,
                    'brand' => $brand,
                    'form' => $form,
                ]);
            }

            $brand->addChannelLink($link);
            $this->dm->persist($link->channel);

            $this->dm->flush(); // flush here too, because it doesn't get a uuid on create
            $this->dispatcher->dispatch(new BrandUpdatedEvent($brand));
            if ($link->channel instanceof Channel) {
                $this->dispatcher->dispatch(new ChannelEvent($link->channel), Events::CHANNEL_UPDATED);
            }
            $this->addFlash('success', $link->type->name.' added');

            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/channel_add.html.twig', [
            'linkType' => $channelType,
            'brand' => $brand,
            'form' => $form,
        ]);
    }

    public function editChannel(Request $request, Brand $brand, ChannelLink $link): Response
    {
        $this->checkPermissions();
        $this->assertLinkBelongsToBrand($brand, $link);

        $form = $this->createForm(ChannelLinkEditType::class, $link->channel, [
            'method' => 'PUT',
            'can_change_type' => false,
            'brand_name' => $brand->getName(),
            'link_default' => (bool) $link->default,
        ]);
        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $link->default = (bool) $form->get('linkDefault')->getData();

            $this->flusher->flush(); // flush here too, because it doesn't get a uuid on create
            if ($link->channel instanceof Channel) {
                $this->dispatcher->dispatch(new ChannelEvent($link->channel), Events::CHANNEL_UPDATED);
            }
            $this->dispatcher->dispatch(new BrandUpdatedEvent($brand));

            $this->flusher->flush();
            $this->addFlash('success', $link->type->name.' updated');

            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/channel_edit.html.twig', [
            'channel' => $link->channel,
            'link' => $link,
            'brand' => $brand,
            'availableConnectors' => $this->availableConnectorsProvider->getAvailableConnectors(),
            'form' => $form,
        ]);
    }

    public function removeChannel(Request $request, Brand $brand, ChannelLink $link): Response
    {
        $this->checkPermissions();
        $this->assertLinkBelongsToBrand($brand, $link);

        $form = $this->createFormBuilder()
            ->setMethod(Request::METHOD_DELETE)
            ->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $brand->removeChannelLink($link);
            $this->dispatcher->dispatch(new BrandUpdatedEvent($brand));

            $this->flusher->flush();
            $this->addFlash('success', $link->type->name.' removed');

            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/channel_remove.html.twig', [
            'brand' => $brand,
            'link' => $link,
            'form' => $form,
        ]);
    }

    private function checkPermissions(): void
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
    }

    private function assertLinkBelongsToBrand(Brand $brand, ChannelLink $link): void
    {
        if (!$brand->hasChannelLink($link)) {
            throw $this->createNotFoundException('Channel link not found for this brand.');
        }
    }
}
