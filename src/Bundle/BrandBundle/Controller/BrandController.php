<?php

namespace Integrated\Bundle\BrandBundle\Controller;

use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Document\LinkType;
use Integrated\Bundle\BrandBundle\Event\BrandAddedEvent;
use Integrated\Bundle\BrandBundle\Event\BrandRemovedEvent;
use Integrated\Bundle\BrandBundle\Event\BrandUpdatedEvent;
use Integrated\Bundle\BrandBundle\Form\Type\BrandType;
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

class BrandController extends AbstractController
{
    /** @param LinkType[] $linkTypes */
    public function __construct(
        private readonly BrandRepository $brands,
        private readonly ChannelRepository $channels,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Flusher $flusher,
        private readonly iterable $linkTypes,
    ) {}

    public function index(): Response
    {
        $this->checkPermissions();

        return $this->render('@IntegratedBrand/brand/index.html.twig', [
            'documents' => $this->brands->all(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->checkPermissions();

        $brand = new Brand();

        $form = $this->createForm(BrandType::class, $brand, ['method' => 'POST']);
        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->brands->add($brand);
            $this->flusher->flush();

            $this->addFlash('success', 'Item created');

            $this->dispatcher->dispatch(new BrandAddedEvent($brand));

            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/edit.html.twig', [
            'form' => $form->createView(),
            'linkTypes' => $this->linkTypes,
        ]);
    }

    public function edit(Request $request, Brand $brand): Response
    {
        $this->checkPermissions();

        $form = $this->createForm(BrandType::class, $brand, ['method' => 'PUT']);
        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);
        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->flusher->flush();

            $this->addFlash('success', 'Item updated');

            $this->dispatcher->dispatch(new BrandUpdatedEvent($brand));


            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        return $this->render('@IntegratedBrand/brand/edit.html.twig', [
            'form' => $form->createView(),
            'linkTypes' => $this->linkTypes,
            'brand' => $brand,
        ]);
    }

    public function delete(Request $request, Brand $brand): Response
    {
        $this->checkPermissions();

        $form = $this->createFormBuilder()
            ->setMethod('DELETE')
            ->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->brands->remove($brand);
            $this->flusher->flush();

            $this->dispatcher->dispatch(new BrandRemovedEvent($brand));

            $this->addFlash('success', 'Brand removed');

            return $this->redirectToRoute('integrated_content_brand_index');
        }

        return $this->render('@IntegratedBrand/brand/delete.html.twig', [
            'brand' => $brand,
            'form' => $form->createView(),
        ]);
    }

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

        $link = new ChannelLink($linkType, null, false);

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
        // @todo ROLE_BRAND_MANAGER ?
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
    }
}
