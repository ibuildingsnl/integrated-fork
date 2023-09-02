<?php

namespace Integrated\Bundle\BrandBundle\Controller;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Document\LinkType;
use Integrated\Bundle\BrandBundle\Event\BrandUpdatedEvent;
use Integrated\Bundle\BrandBundle\Form\Type\ChannelLinkType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Form\Type\ChannelType;
use Integrated\Common\Channel\Event\ChannelEvent;
use Integrated\Common\Channel\Events;
use Integrated\Common\Services\Flusher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelLinkController extends AbstractController
{
    /** @param LinkType[] $linkTypes */
    public function __construct(
        private readonly ChannelRepository $channels,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Flusher $flusher,
        private readonly iterable $linkTypes,
    ) {}

    public function addChannel(Request $request, Brand $brand, string $type): Response
    {
        $this->checkPermissions();

        $linkType = null;
        foreach ($this->linkTypes as $possibleType) {
            if ($possibleType->id === $type) {
                $linkType = $possibleType;
            }
        }
        if (!$linkType instanceof LinkType) {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        $channel = new Channel();
        $channel->setColor($brand->profile->color);
        $channel->setSecondaryColor($brand->profile->secondaryColor);
        $channel->setLogo($brand->profile->logo);

        $link = new ChannelLink($linkType, $channel, false);

        $form = $this->createForm(ChannelLinkType::class, $link, [
            'method' => 'POST',
            'brand_name' => $brand->getName(),
            'allow_choose' => true,
        ]);
        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $brand->addChannelLink($link);
            $this->channels->getDocumentManager()->persist($link->channel);

            $this->flusher->flush();

            $this->addFlash('success', $link->type->name.' added');

            $this->dispatcher->dispatch(new BrandUpdatedEvent($brand));

            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/channel_add.html.twig', [
            'linkType' => $linkType,
            'brand' => $brand,
            'form' => $form->createView(),
        ]);
    }

    public function editChannel(Request $request, Brand $brand, ChannelLink $link): Response
    {
        $this->checkPermissions();

        $form = $this->createForm(ChannelType::class, $link->channel, [
            'method' => 'PUT',
        ]);
        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->flusher->flush();

            $this->addFlash('success', $link->type->name.' updated');

            $this->dispatcher->dispatch(new BrandUpdatedEvent($brand));
            if ($link->channel instanceof Channel) {
                $this->dispatcher->dispatch(new ChannelEvent($link->channel), Events::CHANNEL_UPDATED);
            }

            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/channel_edit.html.twig', [
            'channel' => $link->channel,
            'brand' => $brand,
            'form' => $form->createView(),
        ]);
    }

    public function removeChannel(Request $request, Brand $brand, ChannelLink $link): Response
    {
        $this->checkPermissions();

        $form = $this->createFormBuilder()
            ->setMethod('DELETE')
            ->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $brand->removeChannelLink($link);
            $this->flusher->flush();

            $this->dispatcher->dispatch(new BrandUpdatedEvent($brand));

            $this->addFlash('success', $link->type->name . ' removed');

            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/channel_remove.html.twig', [
            'brand' => $brand,
            'link' => $link,
            'form' => $form->createView(),
        ]);
    }

    private function checkPermissions(): void
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
    }
}
